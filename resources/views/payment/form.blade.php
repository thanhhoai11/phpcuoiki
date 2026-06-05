@extends('layouts.main')

@section('content')
<?php
$pageTitle = 'Thanh toán - Khách Sạn Thiên An';

// Helper format
$fmtVND  = fn($n) => number_format($n, 0, ',', '.') . ' ₫';
$fmtDate = fn($d) => date('d/m/Y', strtotime($d));
$nights  = (new DateTime($booking['check_in']))->diff(new DateTime($booking['check_out']))->days;
?>


<div class="container" style="max-width:1200px;margin:auto;">
<a href="<?= url('/') ?>/?controller=room&action=search" style="color:#212529;text-decoration:none;font-size:14px">← Quay lại</a>
  <h1 class="page-title" style="margin-top:16px">Chọn phương thức thanh toán</h1>
  <p class="page-subtitle">Đặt phòng #<?= $booking['id'] ?> đã được tạo. Hoàn tất thanh toán để xác nhận.</p>
  <div style="background:#fff3cd;border:1px solid #ffeeba;padding:10px;border-radius:8px;margin-top:10px;font-size:14px;">
    Phòng đang được giữ cho bạn trong <strong id="countdown">10:00</strong>
  </div>

  <?php if (!empty($error)): ?>
  <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:40% 60%;gap:24px;justify-items:center;margin-top:20px;">
    <!-- Booking Summary Card -->
    <div class="card mb-2" style="width:100%;max-width:480px;">
      <div class="card-header" style="background:#1A1A2E;">
          <h2 style="color:#fff;text-align:center;">Tóm tắt đặt phòng</h2>
      </div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <?php
          $items = [
            '👤 Tên khách'  => $booking['customer_name'],
            '📞 SĐT'        => $booking['customer_phone'],
            '📧 Email'      => $booking['customer_email'],
            '🏨 Phòng'      => $booking['room_number'] ?? '—',
            '📅 Nhận phòng' => $fmtDate($booking['check_in']),
            '📅 Trả phòng'  => $fmtDate($booking['check_out']),
            '🌙 Số đêm'     => $nights . ' đêm',
            '👥 Số khách'   => $booking['adult_count'] . ' người lớn, ' . $booking['child_count'] . ' trẻ em',
          ];
          ?>
          <?php foreach ($items as $label => $value): ?>
          <div style="background:#FAF8F3;border-radius:8px;padding:12px">
            <div style="font-size:12px;color:#888;margin-bottom:3px"><?= $label ?></div>
            <div style="font-size:15px"><?= htmlspecialchars((string)$value) ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Total -->
        <div style="background:#1A1A2E;border-radius:10px;padding:20px;margin-top:16px;text-align:center;color:#fff">
          <div style="font-size:13px;opacity:.85;margin-bottom:4px">TỔNG TIỀN THANH TOÁN</div>
          <div style="font-size:36px;font-weight:800"><?= $fmtVND($booking['total_price']) ?></div>
        </div>
      </div>
    </div>

    <!-- Payment Methods -->
    <div class="card" >
      <div class="card-header" style="background:#1A1A2E;">
          <h2 style="color:#fff;">Phương thức thanh toán</h2>
      </div>
      <div class="card-body">
        <form action="index.php?controller=payment&action=process" method="POST" id="paymentForm">
          <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

          <!-- MoMo -->
          <label class="payment-option" for="method_momo" style="display:flex;align-items:center;gap:16px;padding:18px;border:2px solid #eee;border-radius:12px;cursor:pointer;margin-bottom:12px;transition:all .2s">
            <input type="radio" name="payment_method" id="method_momo" value="momo" onchange="selectMethod(this)" style="width:20px;height:20px;accent-color:#ae2070">
            <div style="width:52px;height:52px;background:#ae2070;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0">💜</div>
            <div>
              <div style="font-weight:700;font-size:16px">Ví MoMo</div>
              <div style="color:#888;font-size:13px;margin-top:2px">Thanh toán qua ứng dụng MoMo · Nhanh chóng & an toàn</div>
            </div>
            <div style="margin-left:auto;background:#fce4ec;color:#ae2070;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">PHỔ BIẾN</div>
          </label>

          <!-- VietQR -->
          <label class="payment-option" for="method_vietqr" style="display:flex;align-items:center;gap:16px;padding:18px;border:2px solid #eee;border-radius:12px;cursor:pointer;margin-bottom:12px;transition:all .2s">
            <input type="radio" name="payment_method" id="method_vietqr" value="vietqr" onchange="selectMethod(this)" style="width:20px;height:20px;accent-color:#1565C0">
            <div style="width:52px;height:52px;background:#1565C0;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0">🏦</div>
            <div>
              <div style="font-weight:700;font-size:16px">Chuyển khoản VietQR</div>
              <div style="color:#888;font-size:13px;margin-top:2px">Quét mã QR · Hỗ trợ tất cả ngân hàng Việt Nam</div>
            </div>
          </label>

          <!-- Cash -->
          <label class="payment-option" for="method_cash" style="display:flex;align-items:center;gap:16px;padding:18px;border:2px solid #eee;border-radius:12px;cursor:pointer;margin-bottom:20px;transition:all .2s">
            <input type="radio" name="payment_method" id="method_cash" value="cash" onchange="selectMethod(this)" style="width:20px;height:20px;accent-color:#2e7d32">
            <div style="width:52px;height:52px;background:#2e7d32;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0">💵</div>
            <div>
              <div style="font-weight:700;font-size:16px">Tiền mặt tại quầy</div>
              <div style="color:#888;font-size:13px;margin-top:2px">Thanh toán khi nhận phòng · Không mất phí</div>
            </div>
          </label>

          <div id="methodError" class="alert alert-warning" style="display:none">
            ⚠️ Vui lòng chọn phương thức thanh toán.
          </div>

         <div class="text-center">
              <button type="button" onclick="submitPayment()" 
                  class="btn btn-lg"
                  id="payBtn" disabled
                  style="width:320px;padding:14px 0;
                  background:linear-gradient(135deg,#C9A84C,#9A7335);
                  color:#fff;border:none;border-radius:8px">
                  Thanh toán ngay
              </button>
          </div>

          <p style="text-align:center;color:#999;font-size:12px;margin-top:12px">
            Thông tin thanh toán được mã hóa SSL an toàn
          </p>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function selectMethod(radio) {
    document.querySelectorAll('.payment-option').forEach(el => {
      el.style.borderColor = '#eee';
      el.style.background  = '#fff';
    });
    const label = radio.closest('label');
    label.style.borderColor = '#212529';
    label.style.background  = '#f8f9fa';
    document.getElementById('payBtn').disabled = false;
    document.getElementById('methodError').style.display = 'none';

    const methodNames = { momo: 'Thanh toán MoMo', vietqr: 'Tạo mã QR', cash: 'Xác nhận đặt phòng' };
    document.getElementById('payBtn').textContent = methodNames[radio.value] || 'Thanh toán ngay';
  }

  function submitPayment() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    if (!selected) {
      document.getElementById('methodError').style.display = 'flex';
      return;
    }
    isSubmitting = true;
    const btn = document.getElementById('payBtn');
      btn.disabled = true;
      btn.textContent = 'Đang xử lý...';
      document.getElementById('paymentForm').submit();
    }

  Object.keys(localStorage).forEach(key => {
        if (key.startsWith('booking_timer_') && key !== 'booking_timer_<?= $booking['id'] ?>') {
            localStorage.removeItem(key);
        }
    });

    const timerKey = 'booking_timer_<?= $booking['id'] ?>';
    if (!localStorage.getItem(timerKey)) {
        localStorage.setItem(timerKey, Date.now().toString());
    }
    const elapsed = Math.floor((Date.now() - parseInt(localStorage.getItem(timerKey))) / 1000);
    let time = Math.max(0, 900 - elapsed);

    // Nếu timer đã hết từ trước (elapsed > 900) thì không gọi expire lại
    // vì booking có thể đã bị cancel rồi, chỉ redirect về search
    if (time === 0) {
        localStorage.removeItem(timerKey);
        location.href = "<?= url('/') ?>/?controller=room&action=search";
    }

  const timer = setInterval(function() {
      if (time <= 0) {
          clearInterval(timer);
          fetch("<?= url('/') ?>/?controller=booking&action=expire", {
              method: "POST",
              headers: {"Content-Type": "application/x-www-form-urlencoded"},
              body: "booking_id=<?= $booking['id'] ?>"
          }).finally(() => {
              alert("Thời gian giữ phòng đã hết. Vui lòng đặt lại.");
              location.href = "<?= url('/') ?>/?controller=room&action=search";
          });
          return;
      }
      let minutes = Math.floor(time / 60);
      let seconds = time % 60;
      document.getElementById("countdown").innerText =
          minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
      time--;
  }, 1000);

  document.getElementById("countdown").innerText =
      Math.floor(time/60) + ":" + (time%60 < 10 ? "0" : "") + time%60;
  // Huỷ booking khi người dùng thoát ra
  let isSubmitting = false;

  

</script>
</body></html>
@endsection
