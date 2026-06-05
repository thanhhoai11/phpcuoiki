@extends('layouts.main')

@section('content')
<?php
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

$pageTitle = 'Đặt phòng thành công!';

// Lấy danh sách các phòng
$bookingModel = new Booking();
$rooms = $bookingModel->findRoomsByBooking($booking['id']);

// Tạo dữ liệu QR (danh sách nhiều phòng)
$qr_data = "Mã đặt phòng: {$booking['id']}\n";
$qr_data .= "Khách hàng: {$booking['customer_name']}\n";
$qr_data .= "Ngày nhận: " . date('d/m/Y', strtotime($booking['check_in'])) . "\n";
$qr_data .= "Ngày trả: " . date('d/m/Y', strtotime($booking['check_out'])) . "\n";
$qr_data .= "Tổng tiền: {$booking['total_price']} VND\n";
$qr_data .= "Danh sách phòng:\n";

foreach ($rooms as $r) {
    $qr_data .= "- Phòng {$r['room_number']} (Loại: {$r['type_name']})\n";
}

// Tạo mã QR base64 để render trực tiếp
$qr = QrCode::create($qr_data)
     ->setEncoding(new Encoding('UTF-8'))
     ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
     ->setSize(300)
     ->setMargin(10)
     ->setForegroundColor(new Color(0, 0, 0))
     ->setBackgroundColor(new Color(255, 255, 255));

$writer = new PngWriter();
$result = $writer->write($qr);
$qr_base64 = base64_encode($result->getString());

?>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

<style>
    .success-page {
        display: flex;
        align-items: center;
        padding: 40px 0;
        font-family: 'Roboto', sans-serif;
    }
    .success-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        padding: 40px;
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
        transition: transform 0.3s ease;
    }
    .success-card:hover {
        transform: translateY(-5px);
    }
    .success-box {
        display: flex;
        align-items: center;
        justify-content: center; 
        gap: 15px;
        margin: 20px ;
    }
    .success-icon {
        font-size: 2.5rem;
        color: #1e3a8a;    
    }
    .booking-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1e3a8a;
        margin: 0;
    }
    .booking-code {
        background: #f9fafb;
        padding: 15px 30px;
        border-radius: 8px;
        display: inline-block;
        margin: 10px 0;
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e3a8a;
        border: 1px solid #1e3a8a;
    }
    .booking-details {
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        text-align: left;
    }
    .booking-details h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        color: #111827;
        margin-bottom: 15px;
    }
    .booking-details .detail-block {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .booking-details .detail-block h5 {
        font-family: 'Roboto', sans-serif;
        font-size: 1.1rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 5px;
    }
    .booking-details .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    .booking-details .detail-item .label {
        font-weight: 500;
        color: #4b5563;
    }
    .booking-details .detail-item .value {
        font-weight: 600;
        color: #111827;
    }
    .qr-code {
        max-width: 200px;
        margin: 20px auto;
        position: relative;
    }
    .qr-code img {
        width: 100%;
        height: auto;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .email-notification {
        background: #d3e8f9;
        color: #1e3a8a;
        padding: 15px 25px;
        border-radius: 8px;
        margin: 20px 0;
        font-size: 0.95rem;
    }
    .email-notification i {
        margin-right: 10px;
        color: #1e3a8a;
    }
    .action-buttons .btn {
        padding: 12px 30px;
        font-weight: 600;
        margin: 0 10px;
        border-radius: 8px;
    }
    @media print {
        .navbar, .footer, footer, main { margin:0; padding:0; }
        .success-page { padding: 0; min-height: auto; }
        .success-card { box-shadow: none; padding: 20px; }
        .action-buttons { display: none; }
        .email-notification { display: none; }
    }
    @media (max-width: 768px) {
        .success-card { padding: 20px; }
        .booking-title { font-size: 1.5rem; }
        .booking-details h3 { font-size: 1.2rem; }
        .booking-details .detail-item { font-size: 0.9rem; flex-direction: column; align-items: flex-start;}
        .qr-code { max-width: 150px; }
    }
</style>

<div class="success-page">
    <div class="w-100">
        <div class="success-card" data-aos="fade-up" data-aos-duration="1000" style="color:#1A1A2E;">
            <div class="success-box">
                <h1 class="booking-title" style="color:#000;">Đặt Phòng Thành Công!</h1>
            </div>

            <div class="booking-code" style="background:#9A7335;color:#ffffff;">
                <span style="color:#ffffff;">Mã đặt phòng: </span>
                <strong style="color:#ffffff;"><?php echo htmlspecialchars($booking['id']); ?></strong>
            </div>
            
            <div class="email-notification" style="background:#FAF8F3;color:#000;">
                <i class="bi bi-envelope-check" style="color:#000;"></i>
                Email xác nhận đã được gửi đến: <strong style="color:#000;"><?php echo htmlspecialchars($booking['customer_email']); ?></strong>
            </div>

           <div class="booking-details" style="background:#FAF8F3;">
                <h3><i class="bi bi-info-circle me-2"></i>Chi Tiết Đặt Phòng</h3>

                <!-- Customer Information Block -->
                <div class="detail-block">
                    <h5 style="background:#000;color:#fff;padding:8px;border-radius:4px;">
                        <i class="bi bi-person me-2"></i>Thông Tin Khách Hàng
                    </h5>
                    <div class="detail-item">
                        <span class="label">Họ và Tên</span>
                        <span class="value"><?php echo htmlspecialchars($booking['customer_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Email</span>
                        <span class="value"><?php echo htmlspecialchars($booking['customer_email']); ?></span>
                    </div>
                </div>

                <!-- Room Details Block -->
                <div class="detail-block">
                    <h5 style="background:#000;color:#fff;padding:8px;border-radius:4px;">
                        <i class="bi bi-house-door me-2"></i>Chi Tiết Đặt Phòng
                    </h5>
                    <?php foreach ($rooms as $room): ?>
                        <div class="detail-item">
                            <span class="label">Phòng</span>
                            <span class="value">Phòng <?php echo htmlspecialchars($room['room_number']); ?> (<?php echo htmlspecialchars($room['type_name']); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="detail-item">
                        <span class="label">Ngày Nhận Phòng</span>
                        <span class="value"><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Ngày Trả Phòng</span>
                        <span class="value"><?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Số Khách</span>
                        <span class="value"><?php echo htmlspecialchars($booking['adult_count']); ?> người lớn, <?php echo htmlspecialchars($booking['child_count']); ?> trẻ em</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Tổng Tiền</span>
                        <span class="value"><?php echo number_format($booking['total_price'], 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
            </div>
            
            <div class="qr-code" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                <h4 class="mb-3">Mã QR Đặt Phòng</h4>
                <img src="data:image/png;base64,<?php echo $qr_base64; ?>" alt="Mã QR đặt phòng" loading="lazy">
            </div>
            
            <div class="action-buttons mt-4">
                <a href="<?= url('/') ?>/?controller=home" class="btn" 
                    style="background:#fff;color:#C9A84C;border:2px solid #C9A84C;transition:all .2s;"
                    onmouseover="this.style.background='#C9A84C';this.style.color='#fff';"
                    onmouseout="this.style.background='#fff';this.style.color='#C9A84C';">
                        <i class="bi bi-house me-2"></i>Về Trang Chủ
                    </a>
                <button class="btn" onclick="window.print()" style="background:#C9A84C;color:#fff;border:none;">
                    <i class="bi bi-printer me-2"></i>In Thông Tin
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if(typeof AOS !== 'undefined') {
            AOS.init({ once: true, offset: 100 });
        }
    });
</script>
@endsection
