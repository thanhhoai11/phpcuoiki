<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use PHPMailer\PHPMailer\PHPMailer;

use Endroid\QrCode\QrCode; //tạo qrcode
use Endroid\QrCode\Writer\PngWriter; //xuất QR code thành ảnh PNG.
use Endroid\QrCode\Encoding\Encoding; //xác định bảng mã của QR.
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh; //QR bị che 30% vẫn scan được.
use Endroid\QrCode\Color\Color; //đặt màu cho QR code.
 
/**
 * app/services/PaymentService.php
 *
 * Xử lý nghiệp vụ thanh toán: chọn gateway, gọi API, cập nhật DB, gửi mail.
 * Áp dụng Strategy Pattern (gateway) + Factory Pattern (tạo gateway).

 *
 * Các gateway hiện hỗ trợ: cash, vietqr, momo
 */
class PaymentService
{
    private array $cfg; //biến lưu cấu hình thanh toán.

    //Khi tạo object PaymentService, hệ thống truyền config vào.
    public function __construct(array $cfg)
    {
        $this->cfg = $cfg; //truy cập biến $cfg của class.
    }

    // ─────────────────────────────────────────────
    // DÙNG ĐỂ LẤY DANH SÁCH PHƯƠNG THỨC HỖ TRỢ
    // ─────────────────────────────────────────────
    public function getSupportedMethods(): array
    {
        return [
            'cash'   => 'Tiền mặt tại quầy',
            'vietqr' => 'Chuyển khoản VietQR',
            'momo'   => 'Ví MoMo',
        ];
    }

    // ─────────────────────────────────────────────
    // XỬ LÝ THANH TOÁN
    //array $booking: thông tin booking (đơn đặt phòng), method là pttt
    // Nhận vào $booking (array từ DB) + $method
    // Trả về ['success' => bool, 'message' => string, 'pay_url' => ?string, 'qr_code' => ?string]
    // ─────────────────────────────────────────────
    public function process(array $booking, string $method): array
    {
        //ghi log: vd: [INFO] Xử lý thanh toán booking_id=123 method=momo
        $this->log('INFO', "Xử lý thanh toán", ['booking_id' => $booking['id'], 'method' => $method]);

        //câu lệnh điều kiện nhiều nhánh.
        switch ($method) {
            case 'cash':
                return $this->processCash($booking);
            case 'vietqr':
                return $this->processVietQR($booking);
            case 'momo':
                return $this->processMomo($booking);
            default://báo lỗi nếu k phải 3 pttt trên
                throw new \InvalidArgumentException("Phương thức '$method' không được hỗ trợ.");
        }
    }

    // ─────────────────────────────────────────────
    // TIỀN MẶT
    // Xác nhận booking ngay, không cần payment_status = paid
    // (khách trả khi nhận phòng → booking confirmed, payment pending)
    // ─────────────────────────────────────────────
    private function processCash(array $booking): array
    {
        
        //Cập nhật bảng bookings trong database.
        DB::statement(
            "UPDATE bookings SET payment_method = 'cash', status = 'confirmed' WHERE id = ?",
            [$booking['id']]
        );

        //ghi log
        $this->log('INFO', "Thanh toán tiền mặt – booking confirmed", ['booking_id' => $booking['id']]);
        $this->sendBookingConfirmationEmail($booking, 'cash'); //Gửi email xác nhận

        //Trả kết quả cho controller
        return [
            'success' => true,
            'message' => 'Đặt phòng đã được xác nhận. Vui lòng thanh toán tiền mặt khi nhận phòng.',
            'pay_url' => null,
            'qr_code' => null,
        ];
    }

    // ─────────────────────────────────────────────
    // VIETQR
    // Tạo QR offline (img.vietqr.io) – không cần API key
    // Khi có API key thật thì gọi api.vietqr.io
    // ─────────────────────────────────────────────
    private function processVietQR(array $booking): array
    {
        //NẾU config thiếu thì lấy những gtri nãy thay thế
        $vqCfg   = $this->cfg['vietqr'] ?? []; //Lấy cấu hình VietQR
        $bankId  = $vqCfg['bank_id']     ?? 'MB';
        $acctNo  = $vqCfg['account_no']  ?? '0357745893';
        $acctName = $vqCfg['account_name'] ?? 'ROYAL HOTEL';
        $tmpl    = $vqCfg['template']    ?? 'compact2';

        $amount  = (int)$booking['total_price'];//Lấy số tiền cần chuyển
        //Tạo nội dung chuyển khoản
        $addInfo = urlencode('DATPHONG ' . $booking['id'] . ' ' . ($booking['customer_phone'] ?? ''));
        $nameEnc = urlencode($acctName); //Encode tên tài khoản

        //Đây là API tạo QR của VietQR, mở link-> trả về ảnh QR code
        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$acctNo}-{$tmpl}.png"
               . "?amount={$amount}&addInfo={$addInfo}&accountName={$nameEnc}";

        // Cập nhật phương thức thanh toán (trạng thái vẫn pending đến khi xác nhận thực)
        
        //Cập nhật database
        DB::statement(
            "UPDATE bookings SET payment_method = 'vietqr' WHERE id = ?",
            [$booking['id']]
        );

        //Ghi log
        $this->log('INFO', "VietQR tạo thành công", ['booking_id' => $booking['id']]);

        //Trả dữ liệu cho controller
        return [
            'success' => true,
            'message' => 'Quét mã QR bên dưới để chuyển khoản. Sau khi chuyển khoản thành công, nhân viên sẽ xác nhận đặt phòng.',
            'pay_url' => null,
            'qr_code' => $qrUrl,
        ];
    }

    // ─────────────────────────────────────────────
    // MOMO
    // Gọi MoMo API v2 để lấy payUrl, redirect user
    // ─────────────────────────────────────────────
    private function processMomo(array $booking): array
    {
        $momoCfg = $this->cfg['momo'] ?? []; //Lấy cấu hình từ file config.
        $amount  = (string)(int)$booking['total_price']; //Chuẩn bị số tiền
        $orderId = 'MOMO_ORDER_' . strtoupper(uniqid()) . '_BK' . $booking['id'] . '_' . time(); //Tạo orderId ví dụ: MOMO_ORDER_6642AF2D9F_BK25_1715073910
        $requestId = $orderId . '_REQ'; //Tạo requestId
        $orderInfo = 'Thanh toan dat phong #' . $booking['id'] . ' - ' . $booking['customer_name']; //Nội dung thanh toán
        $extraData = base64_encode(json_encode(['booking_id' => $booking['id']]));

        //Chuỗi này dùng để tạo chữ ký bảo mật.
        $rawHash = "accessKey={$momoCfg['access_key']}"
                 . "&amount={$amount}"
                 . "&extraData={$extraData}"
                 . "&ipnUrl={$momoCfg['ipn_url']}"
                 . "&orderId={$orderId}"
                 . "&orderInfo={$orderInfo}"
                 . "&partnerCode={$momoCfg['partner_code']}"
                 . "&redirectUrl={$momoCfg['redirect_url']}"
                 . "&requestId={$requestId}"
                 . "&requestType=payWithMethod";

        //Tạo chữ ký         
        $signature = hash_hmac('sha256', $rawHash, $momoCfg['secret_key']);

        //Tạo payload gửi API
        $payload = [
            'partnerCode' => $momoCfg['partner_code'],
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $momoCfg['redirect_url'],
            'ipnUrl'      => $momoCfg['ipn_url'],
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => 'payWithMethod',
            'signature'   => $signature,
        ];

        //Gửi request đến MoMo
        $response = $this->httpPost($momoCfg['endpoint'], $payload);

        if (isset($response['error']) || ($response['resultCode'] ?? -1) !== 0) { //Kiểm tra lỗi, khác 0 thì k tạo đc
            $this->log('ERROR', "MoMo API thất bại", ['response' => $response, 'booking_id' => $booking['id']]);
            
            //Trả lỗi cho frontend
            return [
                'success' => false,
                'message' => $response['message'] ?? 'Không thể kết nối đến MoMo. Vui lòng thử lại sau.',
                'pay_url' => null,
                'qr_code' => null,
            ];
        }

        // Lưu phương thức thanh toán
        
        DB::statement(
            "UPDATE bookings SET payment_method = 'momo' WHERE id = ?",
            [$booking['id']]
        );

        $this->log('INFO', "MoMo tạo link thành công", ['booking_id' => $booking['id'], 'orderId' => $orderId]);

        return [
            'success' => true,
            'message' => 'Đang chuyển đến trang thanh toán MoMo...',
            'pay_url' => $response['payUrl'],
            'qr_code' => null,
        ];
    }

    // ─────────────────────────────────────────────
    // XỬ LÝ CALLBACK / IPN TỪ MOMO xử lý IPN callback từ MoMo. Tức là: sau khi khách thanh toán xong, MoMo gửi request về server để thông báo kết quả.
    // ─────────────────────────────────────────────
    public function handleMomoCallback(array $data): bool
    {
        $this->log('INFO', "Nhận callback MoMo", $data); //ghilog

        $momoCfg = $this->cfg['momo'] ?? [];

        // Xác minh chữ ký
        if (!$this->verifyMomoSignature($data, $momoCfg['secret_key'] ?? '')) {
            $this->log('ERROR', "Chữ ký MoMo không hợp lệ");
            return false;
        }

        // Trích booking_id từ orderId
        $bookingId = null;
        if (preg_match('/BK(\d+)/', $data['orderId'] ?? '', $m)) {
            $bookingId = (int)$m[1];
        }

        if (!$bookingId) {
            $this->log('ERROR', "Không trích được booking_id từ MoMo callback");
            return false;
        }

        $isPaid = ((int)($data['resultCode'] ?? -1)) === 0;
        

        if ($isPaid) {
            DB::statement(
                "UPDATE bookings SET payment_status = 'paid', status = 'confirmed', payment_method = 'momo' WHERE id = ?",
                [$bookingId]
            );
            $this->log('INFO', "MoMo thanh toán thành công", ['booking_id' => $bookingId]);

            // Gửi email xác nhận thanh toán
            $booking = (new \App\Models\Booking())->findById($bookingId);
            if ($booking) {
                $transId = $data['transId'] ?? 'N/A';
                $this->sendPaymentConfirmationEmail($booking, 'momo', (string)$transId);
            }
        } else {
            DB::statement(
                "UPDATE bookings SET payment_status = 'failed' WHERE id = ?",
                [$bookingId]
            );
            $this->log('WARNING', "MoMo thanh toán thất bại", ['booking_id' => $bookingId, 'resultCode' => $data['resultCode'] ?? '?']);
        }

        return $isPaid;
    }

    // ─────────────────────────────────────────────
    // XÁC MINH CHỮ KÝ MOMO
    // ─────────────────────────────────────────────
    private function verifyMomoSignature(array $data, string $secretKey): bool
    {
        if (empty($secretKey)) return true; // sandbox: bỏ qua nếu chưa cấu hình

        $fields = [
            'accessKey', 'amount', 'extraData', 'message',
            'orderId', 'orderInfo', 'orderType', 'partnerCode',
            'payType', 'requestId', 'responseTime', 'resultCode', 'transId',
        ];
        $parts = [];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $parts[] = "$field={$data[$field]}";
            }
        }
        $rawHash   = implode('&', $parts);
        $signature = hash_hmac('sha256', $rawHash, $secretKey);
        return hash_equals($signature, $data['signature'] ?? '');
    }

    public function confirmVietQRPayment(array $booking): void
    {
        
        DB::statement(
            "UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?",
            [$booking['id']]
        );

        $this->sendPaymentConfirmationEmail($booking, 'vietqr', 'VIETQR_' . $booking['id']);
        $this->log('INFO', 'VietQR xác nhận thanh toán', ['booking_id' => $booking['id']]);
    }

    // ─────────────────────────────────────────────
    // GỬI EMAIL XÁC NHẬN (optional – không block)
    // ─────────────────────────────────────────────
    private function sendBookingConfirmationEmail(array $booking, string $method): void
    {
        try {
            $mailCfg  = $this->cfg['mail'] ?? [];
            $to       = $booking['customer_email'] ?? '';
            $name     = $booking['customer_name']  ?? '';
            $hotel    = $this->cfg['app_name']     ?? 'Khách Sạn';
            $subject  = "✅ Xác nhận đặt phòng #{$booking['id']} – {$hotel}";

            $methodLabel = $this->getSupportedMethods()[$method] ?? $method;
            
            $rooms = (new \App\Models\Booking())->findRoomsByBooking($booking['id']);
            $qr_file = $this->generateQrCode($booking, $rooms);
            
            $body = $this->buildBookingEmailHtml($booking, $rooms, $methodLabel, $hotel);

            $this->sendMail($to, $name, $subject, $body, $mailCfg, $qr_file);
            
            if ($qr_file && file_exists($qr_file)) unlink($qr_file);
        } catch (\Throwable $e) {
            $this->log('WARNING', "Gửi email đặt phòng thất bại: " . $e->getMessage());
        }
    }

    private function sendPaymentConfirmationEmail(array $booking, string $method, string $transId): void
    {
        try {
            $mailCfg = $this->cfg['mail'] ?? [];
            $to      = $booking['customer_email'] ?? '';
            $name    = $booking['customer_name']  ?? '';
            $hotel   = $this->cfg['app_name']     ?? 'Khách Sạn';
            $subject = "💳 Thanh toán thành công – Đặt phòng #{$booking['id']}";

            $rooms = (new \App\Models\Booking())->findRoomsByBooking($booking['id']);
            $qr_file = $this->generateQrCode($booking, $rooms);

            $body = $this->buildPaymentEmailHtml($booking, $rooms, $method, $transId, $hotel);
            $this->sendMail($to, $name, $subject, $body, $mailCfg, $qr_file);
            
            if ($qr_file && file_exists($qr_file)) unlink($qr_file);
        } catch (\Throwable $e) {
            $this->log('WARNING', "Gửi email thanh toán thất bại: " . $e->getMessage());
        }
    }

    private function generateQrCode(array $booking, array $rooms): string
    {
        if (!class_exists('Endroid\QrCode\QrCode')) {
            return '';
        }

        $checkInDate  = date('d/m/Y', strtotime($booking['check_in']));
        $checkOutDate = date('d/m/Y', strtotime($booking['check_out']));

        $qr_data  = "Mã đơn: {$booking['id']}\n";
        $qr_data .= "Khách hàng: {$booking['customer_name']}\n";
        $qr_data .= "Ngày nhận: {$checkInDate}\n";
        $qr_data .= "Ngày trả: {$checkOutDate}\n";
        $qr_data .= "Tổng tiền: " . number_format($booking['total_price']) . " VNĐ\n";
        $qr_data .= "Danh sách phòng:\n";
        foreach ($rooms as $r) {
            $qr_data .= "- Phòng {$r['room_number']} (Loại: {$r['type_name']})\n";
        }

        $qr = \Endroid\QrCode\QrCode::create($qr_data)
            ->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh())
            ->setSize(300)
            ->setMargin(10)
            ->setForegroundColor(new \Endroid\QrCode\Color\Color(0, 0, 0))
            ->setBackgroundColor(new \Endroid\QrCode\Color\Color(255, 255, 255));

        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qr);

        $tempDir = ROOT_PATH . '/public/temp';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $qr_file = $tempDir . "/qr_{$booking['id']}.png";
        file_put_contents($qr_file, $result->getString());

        return $qr_file;
    }

    private function sendMail(string $to, string $name, string $subject, string $body, array $mailCfg, ?string $attachment = null): void
    {
        if (empty($to)) return;

        if (class_exists('PHPMailer\PHPMailer\PHPMailer') && !empty($mailCfg['host'])) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $mailCfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailCfg['username'];
            $mail->Password   = $mailCfg['password'];
            $mail->SMTPSecure = $mailCfg['encryption'] ?? 'tls';
            $mail->Port       = $mailCfg['port'] ?? 587;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($mailCfg['from_email'] ?? $mailCfg['username'], $mailCfg['from_name'] ?? 'Khách Sạn');
            $mail->addAddress($to, $name);
            
            if ($attachment && file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            return;
        }

        // Fallback: PHP mail()
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $from      = $mailCfg['from_email'] ?? ($mailCfg['mail_from'] ?? 'noreply@khachsan.vn');
        $fromName  = $mailCfg['from_name']  ?? ($mailCfg['mail_from_name'] ?? 'Khách Sạn');
        $headers  .= "From: {$fromName} <{$from}>\r\n";
        mail("{$name} <{$to}>", $subject, $body, $headers);
    }

    // ─────────────────────────────────────────────
    // EMAIL HTML TEMPLATES
    // ─────────────────────────────────────────────
    private function buildBookingEmailHtml(array $b, array $rooms, string $methodLabel, string $hotel): string
    {
        $price    = number_format((float)$b['total_price'], 0, ',', '.') . ' ₫';
        $checkIn  = date('d/m/Y', strtotime($b['check_in']));
        $checkOut = date('d/m/Y', strtotime($b['check_out']));
        $nights   = (int)(strtotime($b['check_out']) - strtotime($b['check_in'])) / 86400;

        $roomListHtml = '';
        foreach ($rooms as $r) {
            $roomListHtml .= "<li>Phòng <strong>{$r['room_number']}</strong> - Hạng phòng: {$r['type_name']}</li>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; color: #333333; }
    .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .header { background-color: #1e3a8a; color: #ffffff; padding: 35px 20px; text-align: center; }
    .header h1 { margin: 0; font-size: 26px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
    .header p { margin: 10px 0 0; font-size: 15px; opacity: 0.9; }
    .content { padding: 30px; }
    .greeting { font-size: 18px; margin-bottom: 20px; color: #111827; }
    .booking-id { display: inline-block; background-color: #eff6ff; color: #1d4ed8; padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 18px; margin-bottom: 25px; border: 1px solid #bfdbfe; }
    .section-title { font-size: 16px; font-weight: bold; color: #1e3a8a; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 15px; margin-top: 25px; text-transform: uppercase; letter-spacing: 0.5px; }
    .details-table { width: 100%; border-collapse: collapse; }
    .details-table td { padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 15px; }
    .details-table td:first-child { color: #6b7280; width: 45%; }
    .details-table td:last-child { font-weight: 600; text-align: right; color: #111827; }
    .room-list { background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 15px 0; border: 1px solid #f3f4f6; }
    .room-list ul { margin: 0; padding-left: 20px; color: #4b5563; }
    .room-list li { margin-bottom: 8px; font-size: 15px; }
    .room-list li:last-child { margin-bottom: 0; }
    .total-box { background-color: #1e3a8a; color: #ffffff; padding: 20px; border-radius: 8px; text-align: center; margin-top: 30px; }
    .total-box p { margin: 0; font-size: 14px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; }
    .total-box h2 { margin: 8px 0 0; font-size: 32px; }
    .qr-notice { text-align: center; background-color: #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; margin-top: 25px; font-size: 14px; border: 1px solid #fde68a; line-height: 1.5; }
    .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{$hotel}</h1>
        <p>Xác nhận Đặt phòng Thành công</p>
    </div>
    <div class="content">
        <div class="greeting">Kính gửi <strong>{$b['customer_name']}</strong>,</div>
        <p style="color: #4b5563; font-size: 15px; line-height: 1.6; margin-bottom: 20px;">Cảm ơn quý khách đã tin tưởng và lựa chọn dịch vụ của chúng tôi. Yêu cầu đặt phòng của quý khách đã được xác nhận thành công trên hệ thống.</p>
        
        <div style="text-align: center;">
            <div class="booking-id">Mã Đặt Phòng: #{$b['id']}</div>
        </div>

        <div class="section-title">Chi Tiết Lưu Trú</div>
        <table class="details-table">
            <tr><td>Ngày nhận phòng</td><td>{$checkIn}</td></tr>
            <tr><td>Ngày trả phòng</td><td>{$checkOut}</td></tr>
            <tr><td>Số đêm lưu trú</td><td>{$nights} đêm</td></tr>
            <tr><td>Số lượng khách</td><td>{$b['people']} người</td></tr>
            <tr><td>Phương thức TT</td><td>{$methodLabel}</td></tr>
        </table>

        <div class="section-title">Danh Sách Phòng</div>
        <div class="room-list">
            <ul>{$roomListHtml}</ul>
        </div>

        <div class="total-box">
            <p>Tổng Thanh Toán</p>
            <h2>{$price}</h2>
        </div>

        <div class="qr-notice">
            <strong>Lưu ý quan trọng:</strong> Vui lòng lưu lại email này và xuất trình <strong>Mã QR đính kèm</strong> khi đến làm thủ tục nhận phòng tại quầy Lễ tân.
        </div>
    </div>
    <div class="footer">
        <p>&copy; 2026 {$hotel}. All rights reserved.</p>
        <p>Email này được tạo tự động, vui lòng không trả lời.</p>
    </div>
</div>
</body>
</html>
HTML;
    }

    private function buildPaymentEmailHtml(array $b, array $rooms, string $method, string $transId, string $hotel): string
    {
        $price = number_format((float)$b['total_price'], 0, ',', '.') . ' ₫';
        $methodLabel = $this->getSupportedMethods()[$method] ?? $method;
        
        $roomListHtml = '';
        foreach ($rooms as $r) {
            $roomListHtml .= "<li>Phòng <strong>{$r['room_number']}</strong> - Hạng phòng: {$r['type_name']}</li>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; color: #333333; }
    .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .header { background-color: #059669; color: #ffffff; padding: 35px 20px; text-align: center; }
    .header h1 { margin: 0; font-size: 26px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
    .header p { margin: 10px 0 0; font-size: 15px; opacity: 0.9; }
    .content { padding: 30px; }
    .greeting { font-size: 18px; margin-bottom: 20px; color: #111827; }
    .payment-badge { display: inline-block; background-color: #d1fae5; color: #047857; padding: 10px 20px; border-radius: 30px; font-weight: bold; font-size: 16px; margin-bottom: 25px; border: 1px solid #a7f3d0; letter-spacing: 0.5px; }
    .section-title { font-size: 16px; font-weight: bold; color: #059669; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 15px; margin-top: 25px; text-transform: uppercase; letter-spacing: 0.5px; }
    .details-table { width: 100%; border-collapse: collapse; }
    .details-table td { padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 15px; }
    .details-table td:first-child { color: #6b7280; width: 45%; }
    .details-table td:last-child { font-weight: 600; text-align: right; color: #111827; }
    .room-list { background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 15px 0; border: 1px solid #f3f4f6; }
    .room-list ul { margin: 0; padding-left: 20px; color: #4b5563; }
    .room-list li { margin-bottom: 8px; font-size: 15px; }
    .room-list li:last-child { margin-bottom: 0; }
    .total-box { background-color: #059669; color: #ffffff; padding: 20px; border-radius: 8px; text-align: center; margin-top: 30px; }
    .total-box p { margin: 0; font-size: 14px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; }
    .total-box h2 { margin: 8px 0 0; font-size: 32px; }
    .qr-notice { text-align: center; background-color: #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; margin-top: 25px; font-size: 14px; border: 1px solid #fde68a; line-height: 1.5; }
    .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{$hotel}</h1>
        <p>Giao Dịch Thành Công</p>
    </div>
    <div class="content">
        <div style="text-align: center;">
            <div class="payment-badge">✓ ĐÃ THANH TOÁN</div>
        </div>

        <div class="greeting">Kính gửi <strong>{$b['customer_name']}</strong>,</div>
        <p style="color: #4b5563; font-size: 15px; line-height: 1.6; margin-bottom: 20px;">Cảm ơn quý khách. Khoản thanh toán cho yêu cầu đặt phòng của quý khách đã được ghi nhận thành công trên hệ thống của chúng tôi.</p>
        
        <div class="section-title">Chi Tiết Giao Dịch</div>
        <table class="details-table">
            <tr><td>Mã đặt phòng</td><td>#{$b['id']}</td></tr>
            <tr><td>Mã giao dịch (TransID)</td><td>{$transId}</td></tr>
            <tr><td>Phương thức TT</td><td>{$methodLabel}</td></tr>
            <tr><td>Trạng thái</td><td style="color: #059669;">Thành công</td></tr>
        </table>

        <div class="section-title">Danh Sách Phòng Đã Đặt</div>
        <div class="room-list">
            <ul>{$roomListHtml}</ul>
        </div>

        <div class="total-box">
            <p>Đã Thanh Toán</p>
            <h2>{$price}</h2>
        </div>

        <div class="qr-notice">
            <strong>Lưu ý quan trọng:</strong> Vui lòng lưu lại email này và xuất trình <strong>Mã QR đính kèm</strong> khi đến làm thủ tục nhận phòng tại quầy Lễ tân.
        </div>
    </div>
    <div class="footer">
        <p>&copy; 2026 {$hotel}. All rights reserved.</p>
        <p>Email này được tạo tự động, vui lòng không trả lời.</p>
    </div>
</div>
</body>
</html>
HTML;
    }

    // ─────────────────────────────────────────────
    // HTTP POST (cURL)
    // ─────────────────────────────────────────────
    private function httpPost(string $url, array $data, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_SSL_VERIFYPEER => false, // sandbox only
        ]);
        $body  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->log('ERROR', "cURL error: $error");
            return ['error' => $error];
        }
        return json_decode($body, true) ?? [];
    }

    // ─────────────────────────────────────────────
    // LOGGER (ghi vào storage/logs/app.log)
    // ─────────────────────────────────────────────
    private function log(string $level, string $message, array $context = []): void
    {
        $logDir  = defined('ROOT_PATH') ? ROOT_PATH . '/storage/logs' : __DIR__ . '/../../storage/logs';
        $logFile = $logDir . '/app.log';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        $ctx  = empty($context) ? '' : ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $line = '[' . date('Y-m-d H:i:s') . "] [{$level}] [PaymentService] {$message}{$ctx}" . PHP_EOL;
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}


