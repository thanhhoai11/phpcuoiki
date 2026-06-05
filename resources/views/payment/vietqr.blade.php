@extends('layouts.main')

@section('content')
<?php
$pageTitle  = 'Quét QR Thanh Toán';
$fmtVND     = fn($n) => number_format($n, 0, ',', '.') . ' ₫';
$fmtDate    = fn($d) => date('d/m/Y', strtotime($d));
$qrCode     = $qr_code ?? null;
$nights     = (new DateTime($booking['check_in']))->diff(new DateTime($booking['check_out']))->days;
$roomNumber = $booking['room_number'] ?? '—';
?>

<div class="container" style="max-width:1200px;margin:auto;">
  <h1 class="page-title">Quét mã QR để thanh toán</h1>
  <p class="page-subtitle">Đặt phòng #<?= $booking['id'] ?> · Sử dụng app ngân hàng bất kỳ để quét mã</p>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
    <!-- QR Card -->
    <div class="card" style="text-align:center">
      <div style="background:linear-gradient(135deg,#1565C0,#42a5f5);padding:20px;color:#fff">
        <div style="font-size:18px;font-weight:700">VietQR - Chuyển khoản</div>
        <div style="font-size:13px;opacity:.85;margin-top:4px">Hỗ trợ 40+ ngân hàng</div>
      </div>
      <div class="card-body">
        <?php if ($qrCode): ?>
          <img src="<?= htmlspecialchars($qrCode) ?>"
               alt="VietQR Code"
               style="width:100%;max-width:260px;border-radius:12px;border:3px solid #1565C0">
        <?php else: ?>
          <div style="width:260px;height:260px;background:#f0f0f0;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:60px">
            🏦
          </div>
          <p style="color:#888;margin-top:12px;font-size:13px">Không tạo được mã QR. Vui lòng dùng thông tin chuyển khoản bên cạnh.</p>
        <?php endif; ?>

        <div style="margin-top:16px;background:#e3f2fd;border-radius:8px;padding:12px;text-align:left">
          <div style="font-size:13px;font-weight:700;color:#1565C0;margin-bottom:8px">📋 Nội dung chuyển khoản:</div>
          <code style="font-size:15px;font-weight:700;color:#0d47a1">DATPHONG <?= $booking['id'] ?> <?= $booking['customer_phone'] ?></code>
        </div>

        <div style="background:#fff3cd;border:1px solid #ffeeba;padding:10px;border-radius:8px;margin-top:10px;font-size:14px;text-align:center;">
          Mã QR sẽ hết hạn sau <strong id="countdown">3:00</strong>
        </div>
      </div>
    </div>

    <!-- Info + Steps -->
    <div style="display:flex;flex-direction:column;gap:16px">
      <!-- Amount -->
      <div class="card">
        <div class="card-body" style="text-align:center">
          <div style="color:#666;font-size:15px">Số tiền cần chuyển</div>
          <div style="font-size:32px;font-weight:800;color:#1565C0;margin:8px 0">
            <?= $fmtVND($booking['total_price']) ?>
          </div>
          <div class="badge badge-info"><?= $nights ?> đêm · <?= $booking['people'] ?> khách</div>
        </div>
      </div>

      <!-- Booking Info -->
      <div class="card">
        <div class="card-body">
          <div style="font-size:13px;font-weight:700;color:#666;margin-bottom:10px;text-transform:uppercase">Thông tin đặt phòng</div>
          <?php foreach ([
              '👤 Khách hàng' => $booking['customer_name'],
              '🏨 Phòng'      => $roomNumber,
              '📅 Nhận phòng' => $fmtDate($booking['check_in']),
              '📅 Trả phòng'  => $fmtDate($booking['check_out']),
          ] as $label => $val): ?>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0;font-size:14px">
            <span style="color:#888"><?= $label ?></span>
            <span style="font-weight:600"><?= htmlspecialchars($val) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Steps -->
      <div class="card">
        <div class="card-body">
          <div style="font-size:13px;font-weight:700;color:#666;margin-bottom:12px;text-transform:uppercase">Hướng dẫn thanh toán</div>
          <?php $steps = [
            ['🏦', 'Mở app ngân hàng', 'Chọn quét QR / chuyển khoản'],
            ['📷', 'Quét mã QR',        'Hướng camera vào mã QR bên trái'],
            ['✅', 'Xác nhận',           'Kiểm tra số tiền và xác nhận'],
            ['📧', 'Nhận xác nhận',      'Email xác nhận sẽ được gửi tự động'],
          ]; ?>
          <?php foreach ($steps as [$icon, $title, $desc]): ?>
          <div style="display:flex;gap:12px;margin-bottom:12px;align-items:flex-start">
            <div style="width:32px;height:32px;background:#e3f2fd;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0"><?= $icon ?></div>
            <div>
              <div style="font-weight:600;font-size:14px"><?= $title ?></div>
              <div style="color:#888;font-size:12px"><?= $desc ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Actions -->
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= url('/') ?>/?controller=payment&action=confirmVietQR&id=<?= $booking['id'] ?>"
            class="btn btn-success"
            onclick="isSubmitting = true">
              Tôi đã chuyển khoản xong
          </a>
        <a href="<?= url('/') ?>/?controller=payment&action=form&id=<?= $booking['id'] ?>" 
        class="btn btn-outline"
        onclick="isSubmitting = true">
            ← Đổi phương thức
        </a>
    </div>

  <div class="alert alert-warning" style="margin-top:24px;text-align:center;">
    Lưu ý: Đặt phòng sẽ được xác nhận sau khi chúng tôi nhận được chuyển khoản. Quá trình có thể mất 5-15 phút. Nếu sau 30 phút chưa nhận được email xác nhận, vui lòng liên hệ hotline.
  </div>
</div>

<script>
  localStorage.removeItem('booking_timer_<?= $booking['id'] ?>');
  const timerKey = 'vietqr_timer_<?= $booking['id'] ?>';
  const stored = localStorage.getItem(timerKey);
  const elapsed = stored ? Math.floor((Date.now() - parseInt(stored)) / 1000) : 180;

  if (!stored || elapsed >= 180) {
      localStorage.setItem(timerKey, Date.now().toString());
  }

  let time = Math.max(0, 180 - Math.floor((Date.now() - parseInt(localStorage.getItem(timerKey))) / 1000));

  const timer = setInterval(function() {
      if (time <= 0) {
          clearInterval(timer);
          alert("Mã QR đã hết hạn. Vui lòng đặt lại.");
          location.href = "<?= url('/') ?>/?controller=payment&action=form&id=<?= $booking['id'] ?>";
          return;
      }
      let minutes = Math.floor(time / 60);
      let seconds = time % 60;
      document.getElementById("countdown").innerText =
          minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
      time--;
  }, 1000);

  // Hiển thị ngay lập tức
  document.getElementById("countdown").innerText =
      Math.floor(time/60) + ":" + (time%60 < 10 ? "0" : "") + time%60;

      let isSubmitting = false;

    // Huỷ booking khi out ra khỏi trang QR
    window.addEventListener('beforeunload', function() {
        if (isSubmitting) return;
        navigator.sendBeacon(
            "<?= url('/') ?>/?controller=booking&action=expire",
            new URLSearchParams({ booking_id: "<?= $booking['id'] ?>" })
    );
});
</script>
@endsection
