<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ url('/admin/dashboard') }}" class="list-group-item list-group-item-action">Bảng điều khiển Admin</a>
                <a href="#" class="list-group-item list-group-item-action">Quản lý người dùng</a>
                <a href="{{ url('/admin/priceSettings') }}" class="list-group-item list-group-item-action active">Cài đặt điều chỉnh giá</a>
                <a href="#" class="list-group-item list-group-item-action">Quản lý phòng</a>
                <a href="#" class="list-group-item list-group-item-action">Lịch sử đặt phòng</a>
                <a href="<?= BASE_URL ?>/?controller=admin&action=reports" class="list-group-item list-group-item-action">Báo cáo thống kê</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cài đặt điều chỉnh giá (Lễ/Tết)</h5>
                    <a href="{{ url('/admin/priceSettingsCreate') }}" class="btn btn-sm btn-light">Thêm mới</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên dịp</th>
                                    <th>Từ ngày</th>
                                    <th>Đến ngày</th>
                                    <th>Điều chỉnh</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($settings as $setting)
                                <tr>
                                    <td>{{ $setting['id'] }}</td>
                                    <td>{{ $setting['name'] }}</td>
                                    <td>{{ date('d/m/Y', strtotime($setting['start_date'])) }}</td>
                                    <td>{{ date('d/m/Y', strtotime($setting['end_date'])) }}</td>
                                    <td>
                                        @if($setting['adjustment_type'] == 'percent')
                                            Tăng {{ $setting['adjustment_value'] }}%
                                        @else
                                            Tăng {{ number_format($setting['adjustment_value'], 0, ',', '.') }} VNĐ
                                        @endif
                                    </td>
                                    <td>
                                        @if($setting['status'])
                                            <span class="badge bg-success">Bật</span>
                                        @else
                                            <span class="badge bg-secondary">Tắt</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('/admin/priceSettingsEdit?id='.$setting['id']) }}" class="btn btn-sm btn-primary">Sửa</a>
                                        <form action="{{ url('/admin/priceSettingsDelete') }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cài đặt này?');">
                                            <input type="hidden" name="id" value="{{ $setting['id'] }}">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @if(empty($settings))
                                <tr>
                                    <td colspan="7" class="text-center">Chưa có cài đặt nào.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
