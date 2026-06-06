<?php
/**
 * config/config.php – Cấu hình toàn bộ ứng dụng
 * Thay thế config.php & connect.php cũ
 */
return [
    // ── Database ──────────────────────────────────
    'db_host' => 'localhost',
    'db_name' => 'quanlykhachsan',
    'db_user' => 'root',
    'db_pass' => '',

    // ── App ───────────────────────────────────────
    'app_name'    => 'ROYAL HOTEL',
    'app_email'   => 'info@khachsan.vn',
    'app_phone'   => '0123 456 789',
    'app_address' => '123 Đường ABC, Hà Nội',

    'momo' => [
        'partner_code' => 'MOMO',
        'access_key'   => 'F8BBA842ECF85',
        'secret_key'   => 'K951B6PE1waDMi640xX08PD3vg6EkVlz',
        'endpoint'     => 'https://test-payment.momo.vn/v2/gateway/api/create',
        'redirect_url' => 'http://localhost/payment/public/index.php?route=payment/momo-return',
        'ipn_url'      => 'http://localhost/payment/public/index.php?route=payment/momo-ipn',
    ],

    // VietQR config
    'vietqr' => [
        'bank_id'        => 'MB',              // Mã ngân hàng
        'account_no'     => '0357745893',      // Số tài khoản
        'account_name'   => 'KHACH SAN THIEN AN',
        'api_key'        => 'your-vietqr-api-key',
        'client_id'      => 'your-client-id',
        'template'       => 'compact2',
    ],


    // ── Mail (PHPMailer / SMTP) ────────────────────
    'mail' => [
        'host'       => 'smtp.gmail.com',
        'port'       => 587,
        'username'   => 'hieusontruongthai@gmail.com',
        'password'   => 'msoc hzqc ncxy gsgq',
        'from_email' => 'hieusontruongthai@gmail.com',
        'from_name'  => 'ROYAL HOTEL',
        'encryption' => 'tls',
    ],

    // ── Session ───────────────────────────────────
    'session_lifetime' => 7200, // giây

    // ── Upload ────────────────────────────────────
    'upload_path'   => base_path() . '/public/uploads/',
    'upload_max_mb' => 5,
];
