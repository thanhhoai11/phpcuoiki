<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * app/controllers/Bookingcontroller.php
 */
class Bookingcontroller extends Controller {

    private \App\Models\Booking        $bookingModel;
    private \App\Models\Room           $roomModel;
    private \App\Models\BookingService $bookingService;
    private \App\Models\Payment        $paymentModel;
    private \App\Models\User           $userModel;

    public function __construct() {
        $this->bookingModel   = new \App\Models\Booking();
        $this->roomModel      = new \App\Models\Room();
        $this->bookingService = new \App\Models\BookingService();
        $this->paymentModel   = new \App\Models\Payment();
        $this->userModel      = new \App\Models\User();
    }

    // ─────────────────────────────────────────────
    // FORM ĐẶT PHÒNG
    // Hỗ trợ cả 1 phòng (?room_id=X) lẫn nhiều phòng (?room_ids[]=X&room_ids[]=Y)
    // ─────────────────────────────────────────────
    public function create(): void {
        // ── Bước 1: Chưa đăng nhập → lưu dữ liệu và hỏi xác nhận đăng nhập (giống booking.php của PHP_KT2-master) ──
        if (empty($_SESSION['user_id'])) {
            // Lưu dữ liệu để khôi phục sau
            $_SESSION['pending_booking'] = array_merge($_GET, $_POST);
            $_SESSION['redirect_to_booking'] = true;

            $loginUrl = BASE_URL . '/?controller=auth&action=login';
            echo "
            <script>
                if (confirm('Để đặt phòng, bạn cần đăng nhập. Bạn có muốn chuyển sang trang đăng nhập không?')) {
                    window.location.href = '$loginUrl';
                } else {
                    history.back();
                }
            </script>
            ";
            exit();
        }

        // ── Bước 2: Khôi phục dữ liệu sau khi đăng nhập ──
        if ((isset($_GET['restore']) || isset($_SESSION['restore_booking'])) && isset($_SESSION['pending_booking'])) {
            // Ghi đè cả $_GET và $_POST để đảm bảo các hàm resolveRoomIds() và store() luôn có dữ liệu
            foreach ($_SESSION['pending_booking'] as $key => $value) {
                $_GET[$key] = $value;
                $_POST[$key] = $value;
            }
            unset($_SESSION['pending_booking']);
            unset($_SESSION['restore_booking']);
        }

        // ── Bước 3: Xử lý POST (submit form đặt phòng) ──
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        $checkIn  = $_GET['check_in']  ?? '';
        $checkOut = $_GET['check_out'] ?? '';
        $adults   = max(1, (int)($_GET['adults'] ?? ($_GET['people'] ?? 1)));
        $children = max(0, (int)($_GET['children'] ?? 0));
        $people   = $adults + $children;

        // Lấy thông tin user mới nhất từ DB (giống booking.php của PHP_KT2-master)
        if (!empty($_SESSION['user_id'])) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user) {
                $_SESSION['user'] = $user;
            }
        }

        $roomIds = $this->resolveRoomIds('GET');

        if (empty($roomIds)) {
            $this->setFlash('error', 'Vui lòng chọn ít nhất một phòng hợp lệ.');
            $this->redirectToAction('room', 'search');
            return;
        }

        // Lấy thông tin tất cả phòng — Room::findById() JOIN room_types
        $rooms = [];
        foreach ($roomIds as $id) {
            $r = $this->roomModel->findById($id);
            if ($r) $rooms[] = $r;
        }

        if (empty($rooms)) {
            $this->setFlash('error', 'Phòng không hợp lệ.');
            $this->redirectToAction('room', 'search');
            return;
        }


        $this->render('booking/create', [
            'rooms'    => $rooms,
            'room'     => $rooms[0],
            'roomIds'  => $roomIds,
            'checkIn'  => $checkIn,
            'checkOut' => $checkOut,
            'people'   => $people,
            'adults'   => $adults,
            'children' => $children,
            'methods'  => \App\Models\Payment::METHODS,
            'flash'    => $this->getFlash(),
        ]);
    }

    /**
     * Xử lý POST form đặt phòng (1 hoặc nhiều phòng)
     */
    private function store(): void {
        $checkIn  = trim($_POST['check_in']  ?? '');
        $checkOut = trim($_POST['check_out'] ?? '');
        $adults   = max(1, (int)($_POST['adults'] ?? ($_POST['people'] ?? 1)));
        $children = max(0, (int)($_POST['children'] ?? 0));
        $people   = $adults + $children;

        $roomIds = $this->resolveRoomIds('POST');

        try {
            if (empty($roomIds)) {
                throw new \RuntimeException('Không có phòng nào được chọn.');
            }

            // Validate ngày server-side
            if (!$checkIn || !$checkOut) {
                throw new \InvalidArgumentException('Vui lòng nhập đầy đủ ngày nhận và trả phòng.');
            }
            $ci = new \DateTime($checkIn);
            $co = new \DateTime($checkOut);
            if ($co <= $ci) {
                throw new \InvalidArgumentException('Ngày trả phòng phải sau ngày nhận phòng.');
            }

            // Kiểm tra tất cả phòng còn trống
            foreach ($roomIds as $id) {
                if (!$this->roomModel->isAvailable($id, $checkIn, $checkOut)) {
                    $room  = $this->roomModel->findById($id);
                    $label = $room ? 'Phòng ' . $room['room_number'] : "ID $id";
                    throw new \RuntimeException("$label đã được đặt trong khoảng thời gian này.");
                }
            }

            $builder = (new \App\Models\BookingBuilder())
                ->setUserId($_SESSION['user_id'] ?? null)
                ->setCustomerName(trim($_POST['customer_name']   ?? ''))
                ->setCustomerEmail(trim($_POST['customer_email'] ?? ''))
                ->setCustomerPhone(trim($_POST['customer_phone'] ?? ''))
                ->setCheckIn($checkIn)
                ->setCheckOut($checkOut)
                ->setPeople($people)
                ->setAdults($adults)
                ->setChildren($children)
                ->setRoomId($roomIds[0]);

            $bookingId = $this->bookingService->createBooking($builder, $roomIds);

            $this->redirectToAction('payment', 'form', ['id' => $bookingId]);

        } catch (\Exception $e) {
            $rooms = [];
            foreach ($roomIds as $id) {
                $r = $this->roomModel->findById($id);
                if ($r) $rooms[] = $r;
            }
            $this->render('booking/create', [
                'rooms'    => $rooms,
                'room'     => $rooms[0] ?? null,
                'roomIds'  => $roomIds,
                'error'    => $e->getMessage(),
                'checkIn'  => $checkIn,
                'checkOut' => $checkOut,
                'people'   => $people,
                'adults'   => $adults,
                'children' => $children,
                'methods'  => \App\Models\Payment::METHODS,
                'old'      => $_POST,
            ]);
        }
    }

    /**
     * Đọc room IDs từ GET hoặc POST.
     * Ưu tiên: room_ids[] (multi) → room_id (single)
     */
    private function resolveRoomIds(string $method = 'GET'): array {
        $src = $method === 'POST' ? $_POST : $_GET;

        if (!empty($src['room_ids']) && is_array($src['room_ids'])) {
            return array_map('intval', $src['room_ids']);
        }

        $single = (int)($src['room_id'] ?? 0);
        return $single > 0 ? [$single] : [];
    }

    // ─────────────────────────────────────────────
    // TRANG XÁC NHẬN THÀNH CÔNG
    // ─────────────────────────────────────────────
    public function success(): void {
        $id      = (int)($_GET['id'] ?? 0);
        $booking = $this->bookingModel->findById($id);

        if (!$booking) {
            $this->setFlash('error', 'Không tìm thấy đặt phòng.');
            $this->redirectToAction('home');
            return;
        }

        $this->render('booking/success', [
            'booking' => $booking,
            'flash'   => $this->getFlash(),
        ]);
    }

    // ─────────────────────────────────────────────
    // LỊCH SỬ ĐẶT PHÒNG
    // ─────────────────────────────────────────────
    public function myBookings(): void {
        $this->requireLogin();
        $bookings = $this->bookingModel->findByUser($_SESSION['user_id']);
        $this->render('booking/my_bookings', [
            'bookings' => $bookings,
            'flash'    => $this->getFlash(),
        ]);
    }

    // ─────────────────────────────────────────────
    // HUỶ BOOKING
    // ─────────────────────────────────────────────
    public function cancel(): void {
        $this->requireLogin();
        $id = (int)($_POST['booking_id'] ?? 0);
        if ($this->bookingModel->cancel($id, $_SESSION['user_id'])) {
            $this->setFlash('success', 'Đã huỷ đặt phòng.');
        } else {
            $this->setFlash('error', 'Không thể huỷ đặt phòng này.');
        }
        $this->redirectToAction('booking', 'myBookings');
    }
//Hủy booking khi chưa thanh toán
    public function expire(): void {
        $id = (int)($_POST['booking_id'] ?? 0);
        if (!$id) return;

        $booking = $this->bookingModel->findById($id);
        if ($booking && $booking['status'] === 'pending') {
            $this->bookingModel->updateStatus($id, 'cancelled');
        }
    }

    // ─────────────────────────────────────────────
    // TRANG THANH TOÁN
    // ─────────────────────────────────────────────
    public function payment(): void {
        $id      = (int)($_GET['id'] ?? 0);
        $method  = $_GET['method'] ?? 'vietqr';
        $booking = $this->bookingModel->findById($id);

        if (!$booking) {
            $this->redirectToAction('home');
            return;
        }

        $qrUrl = $method === 'momo'
            ? $this->paymentModel->createMomoLink($id, (float)$booking['total_price'])
            : $this->paymentModel->generateVietQR($id, (float)$booking['total_price']);

        $this->render('booking/payment', [
            'booking' => $booking,
            'method'  => $method,
            'qrUrl'   => $qrUrl,
            'methods' => \App\Models\Payment::METHODS,
            'flash'   => $this->getFlash(),
        ]);
    }

    // ─────────────────────────────────────────────
    // CALLBACK THANH TOÁN
    // ─────────────────────────────────────────────
    public function paymentCallback(): void {
        $id     = (int)($_GET['id'] ?? 0);
        $method = $_GET['method'] ?? 'vietqr';

        $this->paymentModel->markPaid($id, $method);

        $this->setFlash('success', 'Thanh toán thành công!');
        $this->redirectToAction('booking', 'success', ['id' => $id]);
    }

    // ─────────────────────────────────────────────
    // XỬ LÝ CHECK OUT & GỬI EMAIL ĐÁNH GIÁ (SIMULATED FOR RECEPTIONIST)
    // ─────────────────────────────────────────────
    public function checkout(): void {
        $id = (int)($_GET['id'] ?? $_POST['booking_id'] ?? 0);
        $booking = $this->bookingModel->findById($id);

        if (!$booking) {
            $this->setFlash('error', 'Không tìm thấy đặt phòng.');
            $this->redirectToAction('booking', 'myBookings');
            return;
        }

        // Cập nhật trạng thái booking thành 'completed' và actual_check_out thành thời điểm hiện tại
        \Illuminate\Support\Facades\DB::statement(
            "UPDATE bookings SET status = 'completed', actual_check_out = NOW() WHERE id = ?",
            [$id]
        );

        // Gửi email cảm ơn đính kèm link đánh giá phòng
        $emailSent = $this->sendThankYouAndReviewEmail($booking);

        if ($emailSent) {
            $this->setFlash('success', 'Đã check out thành công và gửi email đánh giá đến cho khách hàng!');
        } else {
            $this->setFlash('success', 'Đã check out thành công (nhưng có lỗi xảy ra khi gửi email đánh giá).');
        }

        $this->redirectToAction('booking', 'myBookings');
    }

    /**
     * Gửi email cảm ơn quý khách và đính kèm link đánh giá cho từng loại phòng đã đặt
     */
    private function sendThankYouAndReviewEmail(array $booking): bool {
        $rooms = $this->bookingModel->findRoomsByBooking((int)$booking['id']);
        if (empty($rooms)) {
            return false;
        }

        // Gom các loại phòng duy nhất
        $uniqueRoomTypes = [];
        foreach ($rooms as $r) {
            if (!empty($r['room_type_id'])) {
                $uniqueRoomTypes[$r['room_type_id']] = $r['type_name'];
            }
        }

        $cfg = require ROOT_PATH . '/config/config.php';
        $mailCfg = $cfg['mail'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $mailCfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailCfg['username']; 
            $mail->Password   = $mailCfg['password']; 
            $mail->SMTPSecure = $mailCfg['encryption'] ?? 'tls';
            $mail->Port       = $mailCfg['port'] ?? 587;

            $mail->setFrom($mailCfg['from_email'], $mailCfg['from_name']);
            $mail->addAddress($booking['customer_email'], $booking['customer_name']);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Cảm ơn quý khách đã sử dụng dịch vụ tại ' . $mailCfg['from_name'];

            $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            
            // Xây dựng danh sách các liên kết đánh giá cho từng loại phòng
            $linksHtml = '';
            $secret = 'hotel_review_secret_salt_12345';
            foreach ($uniqueRoomTypes as $typeId => $typeName) {
                $token = hash_hmac('sha256', $booking['id'] . '-' . $typeId, $secret);
                $reviewUrl = $host . BASE_URL . '/?controller=review&action=create&booking_id=' . $booking['id'] . '&room_type_id=' . $typeId . '&token=' . $token;
                
                $linksHtml .= "
                <div style='margin: 15px 0;'>
                    <a href='{$reviewUrl}' style='display: inline-block; background-color: #C9A84C; color: #ffffff; text-decoration: none; padding: 10px 24px; border-radius: 6px; font-weight: bold; font-size: 14px;'>Đánh giá: " . htmlspecialchars($typeName) . "</a>
                </div>";
            }

            $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                    <h2 style='color: #1e3a8a; text-align: center; margin-top: 0;'>Cảm Ơn Quý Khách</h2>
                    <p style='color: #4b5563; line-height: 1.6;'>Kính chào quý khách <strong>" . htmlspecialchars($booking['customer_name']) . "</strong>,</p>
                    <p style='color: #4b5563; line-height: 1.6;'>Chúng tôi xin chân thành cảm ơn quý khách đã lựa chọn và sử dụng dịch vụ lưu trú tại <strong>" . $mailCfg['from_name'] . "</strong> từ ngày <strong>" . $booking['check_in'] . "</strong> đến ngày <strong>" . $booking['check_out'] . "</strong>.</p>
                    <p style='color: #4b5563; line-height: 1.6;'>Sự hài lòng của quý khách là niềm vinh hạnh lớn nhất của chúng tôi. Để cải thiện chất lượng dịch vụ ngày một hoàn hảo hơn, kính mong quý khách dành chút thời gian quý báu để phản hồi và đánh giá về loại phòng quý khách đã sử dụng bằng cách nhấn vào liên kết dưới đây:</p>
                    
                    <div style='text-align: center; margin: 25px 0;'>
                        {$linksHtml}
                    </div>
                    
                    <p style='color: #6b7280; font-size: 13px; text-align: center;'>Đường liên kết đánh giá này dành riêng cho đặt phòng của quý khách.</p>
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;'>
                    <p style='color: #9ca3af; font-size: 12px; text-align: center; margin-bottom: 0;'>
                        Trân trọng,<br><strong>" . $mailCfg['from_name'] . " Team</strong>
                    </p>
                </div>
            </body>
            </html>
            ";

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log("PHPMailer Error in Bookingcheckout: " . $e->getMessage());
            return false;
        }
    }
}




