<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ url('/admin/dashboard') }}" class="list-group-item list-group-item-action active">
                    Bảng điều khiển Admin
                </a>
                <a href="#" class="list-group-item list-group-item-action">Quản lý người dùng</a>
                <a href="{{ url('/admin/priceSettings') }}" class="list-group-item list-group-item-action">Cài đặt điều chỉnh giá</a>
                <a href="#" class="list-group-item list-group-item-action">Quản lý phòng</a>
                <a href="#" class="list-group-item list-group-item-action">Lịch sử đặt phòng</a>
                <a href="<?= BASE_URL ?>/?controller=admin&action=reports" class="list-group-item list-group-item-action">Báo cáo thống kê</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Tổng quan</h5>
                </div>
                <div class="card-body">
                    <p>Chào mừng Admin! Đây là trang quản trị nội bộ.</p>
                </div>
            </div>
        </div>
    </div>
</div>
