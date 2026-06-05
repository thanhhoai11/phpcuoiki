@extends('layouts.main')

@section('content')
<?php $pageTitle = 'Danh Sách Phòng'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Danh Sách Phòng</h2>
    <a href="<?= url('/') ?>/?controller=room&action=search" class="btn btn-primary btn-sm">
        <i class="bi bi-search me-1"></i>Tìm Phòng Trống
    </a>
</div>

<div class="row g-4">
    <?php foreach ($rooms as $room): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm room-card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Phòng <?= htmlspecialchars($room['room_number']) ?></span>
                <span class="badge bg-secondary">Tầng <?= $room['floor'] ?></span>
            </div>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($room['type_name']) ?></h5>
                <p class="card-text text-muted small">
                    <?= htmlspecialchars(mb_substr($room['description'] ?? '', 0, 90)) ?>...
                </p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="fw-bold text-primary fs-5">
                        <?= number_format($room['price'], 0, ',', '.') ?> VNĐ
                        <small class="text-muted fs-6 fw-normal">/đêm</small>
                    </span>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-people"></i> <?= $room['max_guests'] ?> khách
                    </span>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pb-3">
                <a href="<?= url('/') ?>/?controller=room&action=detail&id=<?= $room['id'] ?>"
                   class="btn btn-outline-primary w-100 btn-sm">Xem Chi Tiết</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($rooms)): ?>
    <div class="col-12 text-center py-5">
        <i class="bi bi-door-closed fs-1 text-muted d-block mb-2"></i>
        <p class="text-muted">Chưa có phòng nào trong hệ thống.</p>
    </div>
    <?php endif; ?>
</div>
@endsection
