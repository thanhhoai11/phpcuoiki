<?php

namespace App\Http\Controllers;

/**
 * app/controllers/Paymentcontroller.php
 *
 * Xử lý toàn bộ luồng thanh toán:
 *   GET  ?controller=payment&action=form&id=X        → hiện form chọn PTTT
 *   POST ?controller=payment&action=process          → xử lý PTTT
 *   GET  ?controller=payment&action=success&id=X     → trang thành công
 *   GET  ?controller=payment&action=momoReturn       → MoMo redirect về
 *   POST ?controller=payment&action=momoIpn          → MoMo IPN (server-to-server)
 *
 * KHÔNG chứa logic đặt phòng (việc đó đã thuộc Bookingcontroller).
 */
class Paymentcontroller extends Controller
{
    private \App\Models\Payment        $paymentModel;
    private \App\Services\PaymentService $paymentService;

    public function __construct()
    {
        $cfg = require ROOT_PATH . '/config/config.php';

        $this->paymentModel   = new \App\Models\Payment();
        $this->paymentService = new \App\Services\PaymentService($cfg);
    }

    // ─────────────────────────────────────────────
    // HIỂN THỊ FORM CHỌN PHƯƠNG THỨC THANH TOÁN
    // ─────────────────────────────────────────────
    public function form(): void
    {   
        //lấy và kiểm tra bookingID từ URL (id k hợp lệ ->quay về home)
        $bookingId = (int)($_GET['id'] ?? 0);
        if (!$bookingId) {
            $this->setFlash('error', 'Thiếu mã đặt phòng.');
            $this->redirectToAction('home');
            return;
        }

        //tạo 1 đối tượng Booking, tìm bản ghi theo id (k thấy ->quay về trang chủ)
        $booking = (new \App\Models\Booking())->findById($bookingId);
        if (!$booking) {
            $this->setFlash('error', 'Không tìm thấy đặt phòng.');
            $this->redirectToAction('home');
            return;
        }

        // Kiểm tra thanh toán chưa (Đã thanh toán rồi thì chuyển thẳng sang trang thành công)
        if ($booking['payment_status'] === 'paid') {
            $this->redirectToAction('payment', 'success', ['id' => $bookingId]);
            return;
        }

        // Kiểm tra booking đã bị huỷ chưa: Đã huỷ thì không cho vào trang thanh toán, quay về  danh sách booking của user
        if ($booking['status'] === 'cancelled') {
            $this->setFlash('error', 'Đặt phòng này đã bị huỷ, không thể thanh toán.');
            $this->redirectToAction('booking', 'myBookings');
            return;
        }

        //Render form thanh toán: Nếu qua hết các bước kiểm tra trên → render view payment/form
        $this->render('payment/form', [
            'booking' => $booking,
            'methods' => $this->paymentService->getSupportedMethods(),
            'flash'   => $this->getFlash(),
        ]);
    }

    // ─────────────────────────────────────────────
    // XỬ LÝ SUBMIT CHỌN PHƯƠNG THỨC
    // ─────────────────────────────────────────────
    public function process(): void
    {
        //Nếu không phải request POST (ví dụ ai đó gõ thẳng URL lên trình duyệt) → về home
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToAction('home');
            return;
        }

        //lấy bookingId và paymentmethod mà user gửi lên form
        $bookingId     = (int)($_POST['booking_id'] ?? 0); //(phải là kiểu số nguyên)
        $paymentMethod = trim($_POST['payment_method'] ?? ''); //xóa khoảng trắng

        // Validate cơ bản trước: Nếu thiếu id hoặc chưa chọn phương thức → báo lỗi, quay lại form
        if (!$bookingId || empty($paymentMethod)) {
            $this->setFlash('error', 'Thông tin thanh toán không hợp lệ.');
            $this->redirectToAction('payment', 'form', ['id' => $bookingId]);
            return;
        }

        // Validate phương thức có hỗ trợ không: kiểm tra xem pthuc user chọn có nằm trong sách sách getSupportedMethods() không
        if (!array_key_exists($paymentMethod, $this->paymentService->getSupportedMethods())) {
            $this->setFlash('error', 'Phương thức thanh toán không được hỗ trợ.');
            $this->redirectToAction('payment', 'form', ['id' => $bookingId]);
            return;
        }

        $bookingModel = new \App\Models\Booking();
        $booking      = $bookingModel->findById($bookingId);

        // check booking hợp lệ như ở form 
        if (!$booking) {
            $this->setFlash('error', 'Đặt phòng không tồn tại.');
            $this->redirectToAction('home');
            return;
        }

        if ($booking['status'] === 'cancelled') {
            $this->setFlash('error', 'Đặt phòng này đã bị huỷ.');
            $this->redirectToAction('booking', 'myBookings');
            return;
        }

        if ($booking['payment_status'] === 'paid') {
            $this->setFlash('warning', 'Đặt phòng này đã được thanh toán rồi.');
            $this->redirectToAction('payment', 'success', ['id' => $bookingId]);
            return;
        }

        //xử lý kqua theo từng phương thức: Lỗi xảy ra → bắt lại → ghi log → báo lỗi thân thiện
//→ quay lại form → user thử lại bình thường
        try {
            //Gọi service thực hiện thanh toán thật sự, lấy kết quả từ process bên Paymentservice.php
            $result = $this->paymentService->process($booking, $paymentMethod);

            //Kiểm tra thanh toán có thành công không, thất bại thì quay lại form (chọn pttt)
            if (!$result['success']) {
                $this->setFlash('error', $result['message']);
                $this->redirectToAction('payment', 'form', ['id' => $bookingId]);
                return;
            }

            // MoMo → redirect sang trang thanh toán MoMo, nếu link rỗng hoặc k tồn tại->exit(dừngcodephp)
            if (!empty($result['pay_url'])) { //MoMo trả về một pay_url
                header('Location: ' . $result['pay_url']);//bảo trình duyệt chuyển sang url đó
                exit;
            }

            // VietQR → hiển thị QR trong view
            if (!empty($result['qr_code'])) {  //Nếu kqua k rỗng
                $this->render('payment/vietqr', [ //render trang vietqr.php
                    'booking' => $booking, //truyền vào thông tin, ảnh qr và phần hướng dẫn
                    'qr_code' => $result['qr_code'],
                    'message' => $result['message'],
                ]);
                return;
            }

            // Tiền mặt / các phương thức khác → thành công
            $this->setFlash('success', $result['message']);
            $this->redirectToAction('payment', 'success', ['id' => $bookingId]);

            //Nếu lỗi thì qua catch
        } catch (\Exception $e) {
             // 1. Ghi log lỗi vào file để developer xem sau
            $this->logError('PaymentController::process – ' . $e->getMessage());
            // 2. Báo cho user biết có lỗi
            $this->setFlash('error', 'Có lỗi xảy ra trong quá trình thanh toán: ' . htmlspecialchars($e->getMessage()));//htmlspecialchars() — làm sạch nội dung lỗi trước khi hiển thị, tránh bị tấn công XSS
             // 3. Quay lại form để user thử lại
            $this->redirectToAction('payment', 'form', ['id' => $bookingId]);
        }
    }

    // ─────────────────────────────────────────────
    // Hiển thị TRANG THANH TOÁN THÀNH CÔNG
    // ─────────────────────────────────────────────
    public function success(): void //Chỉ kiểm tra xem booking tồn tại khum
    {
        $bookingId = (int)($_GET['id'] ?? 0); //lấy id từ url 
        $booking   = (new \App\Models\Booking())->findById($bookingId); //tìm booking trong db

        //Kiểm tra booking có tồn tại không
        if (!$booking) {
            $this->setFlash('error', 'Không tìm thấy đặt phòng.');
            $this->redirectToAction('home');
            return;
        }
 
        //Hiển thị trang thành công
        $this->render('payment/success', [
            'booking' => $booking,
            'flash'   => $this->getFlash(),
        ]);
    }

    // ─────────────────────────────────────────────
    // MOMO RETURN URL (sau khi user thanh toán xong)
    // ─────────────────────────────────────────────
    public function momoReturn(): void
    {
        $data = $_GET; //lấy dữ liệu momo gửi về

        try {
            $this->paymentService->handleMomoCallback($data);//gọi service xuer lý: xác minh chữ ký xem dữ liệu MoMo gửi về có hợp lệ không, và cập nhật trạng thái booking trong database thành paid
        } catch (\Exception $e) {
            $this->logError('momoReturn – ' . $e->getMessage());// Nếu có lỗi → ghi log nhưng không return — vẫn chạy tiếp xuống dưới
        }

        // MoMo trả về orderId lấy Lấy bookingId từ orderId
        $bookingId = $this->extractBookingIdFromOrderId($data['orderId'] ?? ''); //hàm extract để tách bookingID ra từ orderid
        
        //nếu lấy đc bookingid thì chuyển sang trang success, không thì về Home
        if ($bookingId) {
            $this->setFlash('success', 'Thanh toán MoMo thành công!');
            $this->redirectToAction('payment', 'success', ['id' => $bookingId]);
        } else {
            $this->redirectToAction('home');
        }
    }

    // ─────────────────────────────────────────────
    // MOMO IPN (server-to-server callback): nhận thông báo thanh toán từ MoMo, xử lý dữ liệu thanh toán và trả kết quả cho MoMo biết server đã nhận thành công hay chưa.
    // ─────────────────────────────────────────────
    public function momoIpn(): void
    {
        $body = file_get_contents('php://input');//Lấy dữ liệu MoMo gửi về, php://input là luồng dữ liệu thô của HTTP request body->MoMo khi gửi callback sẽ gửi JSON trong body của request.
        $data = json_decode($body, true) ?? [];//chuyển JSON → PHP array

        $success = false;//khởi tạo success, mặc định k thánh công

        //Thử xử lý callback từ MoMo
        try {
            $success = $this->paymentService->handleMomoCallback($data); //gọi service để xly dlieu momo sau đó trả về tre/false
        } catch (\Exception $e) {
            $this->logError('momoIpn – ' . $e->getMessage());//lỗi thì ghi lại log
        }

        //Trả HTTP status cho MoMo, true:204, false:400
        http_response_code($success ? 204 : 400);
        exit;
    }


    public function confirmVietQR(): void
    {
        $bookingId = (int)($_GET['id'] ?? 0);
        $booking   = (new \App\Models\Booking())->findById($bookingId);

        if (!$booking) {
            $this->setFlash('error', 'Không tìm thấy đặt phòng.');
            $this->redirectToAction('home');
            return;
        }

        if ($booking['payment_status'] === 'paid') {
            $this->redirectToAction('payment', 'success', ['id' => $bookingId]);
            return;
        }

        // Gọi service xử lý update DB + gửi email
        $this->paymentService->confirmVietQRPayment($booking);

        $this->setFlash('success', 'Xác nhận thành công! Email xác nhận đã được gửi.');
        $this->redirectToAction('payment', 'success', ['id' => $bookingId]);
    }

    // ─────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────

    /** Trích booking_id từ orderId dạng "MOMO_ORDER_XXX_BK12_timestamp" */
    private function extractBookingIdFromOrderId(string $orderId): ?int
    {
        if (preg_match('/BK(\d+)/', $orderId, $m)) { //Dùng Regex để tách số (BK(\d+): chuỗi bắt đầu bằng BK, \d: chữ số, +: 1 hặc nhiều chữ số, (): nhóm lấy kết quả)
            return (int)$m[1]; //Trả về bookingId, ép kiểu int
        }
        return null; //nếu k match với BK(\d+) trả về null
    }

    /** Ghi log lỗi vào storage/logs/app.log */ //Hàm này dùng để ghi lỗi vào file log.
    private function logError(string $message): void
    {
        //Khai báo đường dẫn log
        $logDir  = ROOT_PATH . '/storage/logs';
        $logFile = $logDir . '/app.log';

        //Kiểm tra thư mục tồn tại chưa
        if (!is_dir($logDir)) { //is_dir: kiểm tra thư mục tồn tại
            mkdir($logDir, 0775, true); //mkdir: tạo thư mục (0775: quyền truy cập, true: tạo nhiều cấp thư mục) Kiểu nếu chưa có thì tự tạo
        }

        //Tạo dòng log
        $line = '[' . date('Y-m-d H:i:s') . '] [ERROR] [Paymentcontroller] ' . $message . PHP_EOL;
        //Ghi vào file
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX); //FILE_APPEN: ghi thêm vào cuối file, LOCK_EX:	khóa file khi ghi
    }
}


