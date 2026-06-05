@extends('layouts.auth')

@section('content')
<div class="auth-card" data-aos="fade-up" data-aos-duration="1000">
    <div class="hotel-brand">Royal Hotel</div>
    <p class="subtitle">Khách sạn & Khu nghỉ dưỡng sang trọng</p>

    <form action="<?= url('/') ?>/?controller=auth&action=login" method="POST" autocomplete="on" novalidate>
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
                <a href="<?= url('/') ?>/?controller=auth&action=forgotPassword" style="font-size: 0.85rem; color: #64748b;">Quên mật khẩu?</a>
            </div>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">Đăng Nhập</button>
        </div>

        <div class="divider">HOẶC</div>

        <div class="text-center">
            Chưa có tài khoản? <a href="<?= url('/') ?>/?controller=auth&action=register">Đăng ký ngay</a>
        </div>
        <div class="text-center mt-3">
            <a href="<?= url('/') ?>/?controller=home" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">
                <i class="bi bi-arrow-left"></i> Quay lại Trang chủ
            </a>
        </div>
    </form>
</div>

@endsection
