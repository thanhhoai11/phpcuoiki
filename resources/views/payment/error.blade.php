@extends('layouts.main')

@section('content')
<?php $pageTitle = 'Lỗi - Khách Sạn ROYAL HOTEL'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<div class="container-sm" style="text-align:center;padding-top:60px">
  <div style="font-size:80px;margin-bottom:16px">⚠️</div>
  <h1 style="font-size:28px;font-weight:800;color:#e74c3c;margin-bottom:12px">Đã xảy ra lỗi</h1>
  <p style="color:#666;font-size:16px;margin-bottom:32px"><?= htmlspecialchars($message ?? 'Lỗi không xác định.') ?></p>
  <a href="index.php?route=booking" class="btn btn-primary btn-lg">← Quay lại trang đặt phòng</a>
</div>

</body></html>

@endsection
