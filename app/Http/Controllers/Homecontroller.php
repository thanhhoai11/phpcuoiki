<?php

namespace App\Http\Controllers;

/**
 * app/controllers/Homecontroller.php
 * Trang chủ và các trang tĩnh
 */
class Homecontroller extends Controller {

    private \App\Models\Room $roomModel;

    public function __construct() {
        $this->roomModel = new \App\Models\Room();
    }

    /**
     * GET ?controller=home  →  Trang chủ
     */
    public function index(): void {
        // Lấy tất cả loại phòng để hiển thị nổi bật
        $roomTypes = $this->roomModel->allTypes();

        $this->render('home/index', [
            'roomTypes' => $roomTypes,
            'flash'     => $this->getFlash(),
        ]);
    }

    /**
     * GET ?controller=home&action=about  →  Giới thiệu
     */
    public function about(): void {
        $this->render('home/about', ['flash' => $this->getFlash()]);
    }

    /**
     * GET/POST ?controller=home&action=contact  →  Liên hệ
     */
    public function contact(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name    = trim($_POST['name']    ?? '');
            $email   = trim($_POST['email']   ?? '');
            $message = trim($_POST['message'] ?? '');

            // TODO: Gửi email liên hệ (PHPMailer)
            error_log("CONTACT: $name <$email> – $message");

            $this->render('home/contact', [
                'success' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm.'
            ]);
            return;
        }

        $this->render('home/contact', ['flash' => $this->getFlash()]);
    }
}

