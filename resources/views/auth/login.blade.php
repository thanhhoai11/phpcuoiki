<div class="auth-card" data-aos="fade-up" data-aos-duration="1000">
    <div class="hotel-brand">Royal Hotel</div>
    <p class="subtitle">Khách sạn &amp; Khu nghỉ dưỡng sang trọng</p>

    {{-- ===== CUSTOMER LOGIN (mặc định) ===== --}}
    <div id="customer-card">
        <form action="<?= BASE_URL ?>/?controller=auth&action=login" method="POST" autocomplete="on">
            <input type="hidden" name="role" value="customer">
            <div class="mb-4 text-start">
                <label for="email" class="form-label" style="font-weight: 600; color: #1e293b; font-size: 0.9rem; margin-bottom: 8px;">Email</label>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Nhập email của bạn..."
                    required
                    autocomplete="email"
                    inputmode="email"
                    style="padding-left: 15px;"
                >
            </div>
            <div class="mb-4 text-start">
                <label for="password" class="form-label" style="font-weight: 600; color: #1e293b; font-size: 0.9rem; margin-bottom: 8px;">Mật khẩu</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Nhập mật khẩu..."
                        required
                        autocomplete="current-password"
                        style="padding-left: 15px;"
                    >
                    <button type="button" class="toggle-password" aria-label="Hiện/Ẩn mật khẩu">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="text-end mt-2">
                    <a href="<?= BASE_URL ?>/?controller=auth&action=forgotPassword" style="font-size: 0.85rem; color: #64748b;">Quên mật khẩu?</a>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">Đăng Nhập</button>
            </div>

            <div class="divider">HOẶC</div>

            <div class="text-center">
                Chưa có tài khoản? <a href="<?= BASE_URL ?>/?controller=auth&action=register">Đăng ký ngay</a>
            </div>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/?controller=home" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">
                    <i class="bi bi-arrow-left"></i> Quay lại Trang chủ
                </a>
            </div>
        </form>
        <!-- Link chuyển sang form nhân viên -->
        <p class="mt-3 text-center" style="font-size: 0.85rem; color: #64748b;">
            Nếu bạn là nhân viên? <a href="#" id="show-staff-link" style="color: #2563eb; text-decoration: none; font-weight: 600;">Đăng nhập ngay</a>
        </p>
    </div>

    {{-- ===== STAFF LOGIN (ẩn mặc định) ===== --}}
    <div id="staff-card" class="d-none">
        <form action="<?= BASE_URL ?>/?controller=auth&action=login" method="POST" autocomplete="on" novalidate>
            <input type="hidden" name="role" value="staff">
            <div class="mb-4 text-start">
                <label for="staff-username" class="form-label" style="font-weight: 600; color: #1e293b; font-size: 0.9rem; margin-bottom: 8px;">Tên đăng nhập</label>
                <input
                    type="text"
                    class="form-control"
                    id="staff-username"
                    name="username"
                    placeholder="Nhập tên đăng nhập..."
                    required
                    autocomplete="username"
                    style="padding-left: 15px;"
                >
            </div>
            <div class="mb-4 text-start">
                <label for="staff-password" class="form-label" style="font-weight: 600; color: #1e293b; font-size: 0.9rem; margin-bottom: 8px;">Mật khẩu</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        class="form-control"
                        id="staff-password"
                        name="password"
                        placeholder="Nhập mật khẩu..."
                        required
                        autocomplete="current-password"
                        style="padding-left: 15px;"
                    >
                    <button type="button" class="toggle-password" aria-label="Hiện/Ẩn mật khẩu">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-success">Đăng Nhập</button>
            </div>
        </form>
        <!-- Link quay về form khách hàng -->
        <p class="mt-3 text-center" style="font-size: 0.85rem; color: #64748b;">
            Bạn là khách hàng? <a href="#" id="show-customer-link" style="color: #2563eb; text-decoration: none; font-weight: 600;">Đăng nhập tại đây</a>
        </p>
    </div>
</div>

<script>
    // Toggle giữa 2 card
    const customerCard = document.getElementById('customer-card');
    const staffCard    = document.getElementById('staff-card');

    document.getElementById('show-staff-link').addEventListener('click', function(e) {
        e.preventDefault();
        customerCard.classList.add('d-none');
        staffCard.classList.remove('d-none');
    });

    document.getElementById('show-customer-link').addEventListener('click', function(e) {
        e.preventDefault();
        staffCard.classList.add('d-none');
        customerCard.classList.remove('d-none');
    });

    // Toggle hiện/ẩn mật khẩu
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = btn.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });
    });
</script>
