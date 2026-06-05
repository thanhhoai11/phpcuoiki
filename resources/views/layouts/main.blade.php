<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Khách Sạn') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        h1, h2, h3, h4, h5, .page-title { font-family: 'Cormorant Garamond', serif; }
    </style>
    <style>
        body { background-color: #f8f9fa; display: flex; flex-direction: column; min-height: 100vh; }
        .navbar-brand { font-weight: 700; letter-spacing: .5px; }
        .room-card { transition: transform .2s, box-shadow .2s; }
        .room-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12)!important; }
        footer { border-top: 1px solid #dee2e6;margin-top: auto !important; }
        main { flex: 1 0 auto; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?= url('/') ?>/?controller=home">
            <i class="bi bi-building me-1"></i>ROYAL HOTEL
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/') ?>/?controller=home">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/') ?>/?controller=home&action=about">Giới thiệu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/') ?>/?controller=room&action=amenities">Tiện nghi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/') ?>/?controller=home&action=contact">Liên hệ</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (!empty(session('user_id'))): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars(session('user.fullname') ?? 'Tài khoản') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('/') ?>/?controller=booking&action=myBookings">
                            <i class="bi bi-calendar-check me-1"></i>Đặt phòng của tôi</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('/') ?>/?controller=auth&action=logout">
                            <i class="bi bi-box-arrow-right me-1"></i>Đăng xuất</a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/') ?>/?controller=auth&action=login">Đăng nhập</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm ms-2 my-auto"
                       href="<?= url('/') ?>/?controller=auth&action=register">Đăng ký</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- FLASH MESSAGE -->
<?php if (!empty($flash)): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- NỘI DUNG CHÍNH -->
<main class="py-4">
    <div class="container">
        @yield('content')
    </div>
</main>

<!-- FOOTER -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-4">
                <h6 class="fw-bold"><i class="bi bi-building me-1"></i>ROYAL HOTEL</h6>
                <p class="text-light small mb-0">Dịch vụ lưu trú cao cấp, phục vụ tận tình 24/7.</p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold">Liên hệ</h6>
                <p class="text-light small mb-1"><i class="bi bi-geo-alt me-1"></i>12 Chùa Bộc, Hà Nội</p>
                <p class="text-light small mb-1"><i class="bi bi-telephone me-1"></i>0123 456 789</p>
                <p class="text-light small mb-0"><i class="bi bi-envelope me-1"></i>royalhotel@gmail.com.vn</p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold">Liên kết nhanh</h6>
                <ul class="list-unstyled small">
                    <li><a class="text-light text-decoration-none" href="<?= url('/') ?>/?controller=home#rooms-section">Danh sách phòng</a></li>
                    <li><a class="text-light text-decoration-none" href="<?= url('/') ?>/?controller=room&action=search">Tìm phòng trống</a></li>
                    <li><a class="text-light text-decoration-none" href="<?= url('/') ?>/?controller=home&action=contact">Liên hệ</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary mt-3 mb-2">
        <p class="text-center text-light small mb-0">&copy; <?= date('Y') ?> ROYAL HOTEL. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>