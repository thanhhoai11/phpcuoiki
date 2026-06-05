@extends('layouts.main')

@section('content')
<?php $pageTitle = 'Đặt Phòng Của Tôi'; ?>

<h2 class="fw-bold mb-4">Đặt Phòng Của Tôi</h2>

<?php if (empty($bookings)): ?>
<div class="text-center py-5">
    <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
    <p class="text-muted mb-3">Bạn chưa có đặt phòng nào.</p>
    <a href="<?= url('/') ?>/?controller=room&action=search" class="btn btn-primary">
        Tìm Phòng Ngay
    </a>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Phòng</th>
                <th>Nhận phòng</th>
                <th>Trả phòng</th>
                <th>Số khách</th>
                <th>Tổng tiền</th>
                <th>Thanh toán</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b):
                $badgeMap = [
                    'pending'   => 'warning',
                    'confirmed' => 'success',
                    'cancelled' => 'secondary',
                    'rejected'  => 'danger',
                    'completed' => 'info',
                ];
                $badge = $badgeMap[$b['status']] ?? 'secondary';
                $payBadge = $b['payment_status'] === 'paid' ? 'success' : 'warning';
            ?>
            <tr>
                <td>#<?= $b['id'] ?></td>
                <td>
                    <div class="fw-semibold">Phòng <?= htmlspecialchars($b['room_number'] ?? '–') ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($b['type_name'] ?? '') ?></div>
                </td>
                <td><?= $b['check_in'] ?></td>
                <td><?= $b['check_out'] ?></td>
                <td class="text-center"><?= $b['adult_count'] ?> NL, <?= $b['child_count'] ?> TE</td>
                <td class="fw-bold"><?= number_format($b['total_price'], 0, ',', '.') ?> VNĐ</td>
                <td>
                    <span class="badge bg-<?= $payBadge ?>">
                        <?= $b['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                    </span>
                </td>
                <td>
                    <span class="badge bg-<?= $badge ?>"><?= $b['status'] ?></span>
                </td>
                <td>
                    <?php if ($b['status'] === 'pending' && $b['payment_status'] !== 'paid'): ?>
                    <div class="d-flex gap-1">
                        <a href="<?= url('/') ?>/?controller=booking&action=payment&id=<?= $b['id'] ?>"
                           class="btn btn-sm btn-outline-success">Thanh toán</a>
                        <form method="POST" action="<?= url('/') ?>/?controller=booking&action=cancel"
                              onsubmit="return confirm('Huỷ đặt phòng này?')">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Huỷ</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
@endsection
