<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * app/models/Payment.php
 *
 * CSDL không có bảng payments riêng.
 * Thông tin thanh toán nằm trong bảng bookings:
 *   payment_method  varchar(100)
 *   payment_status  varchar(50)  DEFAULT 'pending'
 *
 * Class này tập trung logic thanh toán (VietQR, MOMO).
 */
class Payment {

    

    // Các phương thức thanh toán hợp lệ
    public const METHODS = [
        'cash'          => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
        'credit_card'   => 'Thẻ tín dụng',
        'momo'          => 'Ví MoMo',
        'vietqr'        => 'VietQR',
    ];

    

    /**
     * Cập nhật phương thức & trạng thái thanh toán cho booking
     */
    public function markPaid(int $bookingId, string $method): void {
        DB::statement(
            "UPDATE bookings SET payment_method = ?, payment_status = 'paid' WHERE id = ?",
            [$method, $bookingId]
        );
    }

    /**
     * Tạo URL thanh toán VietQR
     * Dùng API vietqr.io (miễn phí, không cần key)
     */
    public function generateVietQR(int $bookingId, float $amount): string {
        $bankId    = 'MB';           // Thay bằng mã ngân hàng thực tế
        $accountNo = '0123456789';  // Thay bằng số TK thực tế
        $desc      = urlencode("Dat phong #$bookingId");
        $amt       = (int)$amount;
        return "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png"
             . "?amount={$amt}&addInfo={$desc}&accountName=Khach%20San";
    }

    /**
     * Tạo link MOMO (giả lập - cần API key thật của MOMO)
     */
    public function createMomoLink(int $bookingId, float $amount): string {
        $returnUrl = BASE_URL . '/?controller=booking&action=paymentCallback&id=' . $bookingId;
        // TODO: Gọi MOMO API thật, ở đây trả về link demo
        return "https://test-payment.momo.vn/pay?amount={$amount}&orderId={$bookingId}"
             . "&returnUrl=" . urlencode($returnUrl);
    }
}
