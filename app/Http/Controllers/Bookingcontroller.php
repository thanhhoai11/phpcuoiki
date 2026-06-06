<?php

namespace App\Http\Controllers;

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
        $people   = (int)($_GET['people'] ?? 1);

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


        $priceSettings = (new \App\Models\PriceSetting())->getActive();

        $this->render('booking/create', [
            'rooms'    => $rooms,
            'room'     => $rooms[0],
            'roomIds'  => $roomIds,
            'checkIn'  => $checkIn,
            'checkOut' => $checkOut,
            'people'   => $people,
            'methods'  => \App\Models\Payment::METHODS,
            'flash'    => $this->getFlash(),
            'priceSettings' => $priceSettings,
        ]);
    }

    /**
     * Xử lý POST form đặt phòng (1 hoặc nhiều phòng)
     */
    private function store(): void {
        $checkIn  = trim($_POST['check_in']  ?? '');
        $checkOut = trim($_POST['check_out'] ?? '');
        $people   = (int)($_POST['people']   ?? 1);

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
                ->setRoomId($roomIds[0]);

            $bookingId = $this->bookingService->createBooking($builder, $roomIds);

            $this->redirectToAction('payment', 'form', ['id' => $bookingId]);

        } catch (\Exception $e) {
            $rooms = [];
            foreach ($roomIds as $id) {
                $r = $this->roomModel->findById($id);
                if ($r) $rooms[] = $r;
            }
            $priceSettings = (new \App\Models\PriceSetting())->getActive();
            $this->render('booking/create', [
                'rooms'    => $rooms,
                'room'     => $rooms[0] ?? null,
                'roomIds'  => $roomIds,
                'error'    => $e->getMessage(),
                'checkIn'  => $checkIn,
                'checkOut' => $checkOut,
                'people'   => $people,
                'methods'  => \App\Models\Payment::METHODS,
                'old'      => $_POST,
                'priceSettings' => $priceSettings,
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
}




