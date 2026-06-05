<?php

namespace App\Http\Controllers;

class Reviewcontroller extends Controller {

    private \App\Models\Booking $bookingModel;
    private \App\Models\Room    $roomModel;
    private \App\Models\Review  $reviewModel;

    public function __construct() {
        $this->bookingModel = new \App\Models\Booking();
        $this->roomModel    = new \App\Models\Room();
        $this->reviewModel  = new \App\Models\Review();
    }

    /**
     * GET ?controller=review&action=create&booking_id=X&room_type_id=Y&token=Z
     */
    public function create(): void {
        $bookingId  = (int)($_GET['booking_id']  ?? 0);
        $roomTypeId = (int)($_GET['room_type_id'] ?? 0);
        $token      = trim($_GET['token']        ?? '');

        if (!$bookingId || !$roomTypeId || !$token) {
            $this->setFlash('error', 'Đường dẫn đánh giá không hợp lệ.');
            $this->redirectToAction('home', 'index');
            return;
        }

        // Kiểm tra chữ ký bảo mật token
        $secret = 'hotel_review_secret_salt_12345';
        $expectedToken = hash_hmac('sha256', $bookingId . '-' . $roomTypeId, $secret);
        if ($token !== $expectedToken) {
            $this->setFlash('error', 'Mã xác thực đánh giá không chính xác.');
            $this->redirectToAction('home', 'index');
            return;
        }

        // Lấy thông tin booking
        $booking = $this->bookingModel->findById($bookingId);
        if (!$booking || $booking['status'] !== 'completed') {
            $this->setFlash('error', 'Đặt phòng này chưa hoàn thành hoặc không tồn tại.');
            $this->redirectToAction('home', 'index');
            return;
        }

        // Lấy thông tin loại phòng
        $roomType = $this->roomModel->findTypeById($roomTypeId);
        if (!$roomType) {
            $this->setFlash('error', 'Loại phòng đánh giá không tồn tại.');
            $this->redirectToAction('home', 'index');
            return;
        }

        // Yêu cầu đăng nhập tài khoản khách hàng
        $this->requireLogin();
        $user = $this->currentUser();

        // Kiểm tra quyền sở hữu booking (so trùng user_id hoặc email đăng ký)
        if ((int)$booking['user_id'] !== (int)$user['id'] && strtolower($booking['customer_email']) !== strtolower($user['email'])) {
            $this->setFlash('error', 'Tài khoản của bạn không được phép đánh giá đặt phòng này.');
            $this->redirectToAction('home', 'index');
            return;
        }

        $this->render('review/create', [
            'booking'    => $booking,
            'roomType'   => $roomType,
            'token'      => $token,
            'flash'      => $this->getFlash(),
        ]);
    }

    /**
     * POST ?controller=review&action=store
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToAction('home', 'index');
            return;
        }

        $bookingId  = (int)($_POST['booking_id']  ?? 0);
        $roomTypeId = (int)($_POST['room_type_id'] ?? 0);
        $token      = trim($_POST['token']        ?? '');

        if (!$bookingId || !$roomTypeId || !$token) {
            $this->setFlash('error', 'Dữ liệu không hợp lệ.');
            $this->redirectToAction('home', 'index');
            return;
        }

        // Kiểm tra chữ ký bảo mật token
        $secret = 'hotel_review_secret_salt_12345';
        $expectedToken = hash_hmac('sha256', $bookingId . '-' . $roomTypeId, $secret);
        if ($token !== $expectedToken) {
            $this->setFlash('error', 'Mã xác thực đánh giá không chính xác.');
            $this->redirectToAction('home', 'index');
            return;
        }

        // Lấy thông tin booking
        $booking = $this->bookingModel->findById($bookingId);
        if (!$booking || $booking['status'] !== 'completed') {
            $this->setFlash('error', 'Đặt phòng này chưa hoàn thành hoặc không tồn tại.');
            $this->redirectToAction('home', 'index');
            return;
        }

        // Yêu cầu đăng nhập tài khoản khách hàng
        $this->requireLogin();
        $user = $this->currentUser();

        // Kiểm tra quyền sở hữu booking
        if ((int)$booking['user_id'] !== (int)$user['id'] && strtolower($booking['customer_email']) !== strtolower($user['email'])) {
            $this->setFlash('error', 'Tài khoản của bạn không được phép đánh giá đặt phòng này.');
            $this->redirectToAction('home', 'index');
            return;
        }

        $rating  = (int)($_POST['rating']  ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $this->setFlash('error', 'Vui lòng chọn số sao đánh giá từ 1 đến 5.');
            $this->redirectToAction('review', 'create', [
                'booking_id'   => $bookingId,
                'room_type_id' => $roomTypeId,
                'token'        => $token
            ]);
            return;
        }

        try {
            $this->reviewModel->create([
                'user_id'      => $user['id'],
                'room_type_id' => $roomTypeId,
                'rating'       => $rating,
                'comment'      => $comment,
            ]);

            $this->setFlash('success', 'Cảm ơn quý khách đã gửi đánh giá trải nghiệm dịch vụ!');
            $this->redirectToAction('room', 'detail', ['id' => $roomTypeId]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Đã xảy ra lỗi khi lưu đánh giá: ' . $e->getMessage());
            $this->redirectToAction('review', 'create', [
                'booking_id'   => $bookingId,
                'room_type_id' => $roomTypeId,
                'token'        => $token
            ]);
        }
    }
}
