<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Thêm cài đặt điều chỉnh giá</h5>
                </div>
                <div class="card-body">
                    <form action="{{ url('/admin/priceSettingsCreate') }}" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên dịp lễ / sự kiện</label>
                            <input type="text" name="name" class="form-control" required placeholder="VD: Quốc khánh 2/9">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Từ ngày</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Đến ngày</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kiểu điều chỉnh</label>
                                <select name="adjustment_type" class="form-select">
                                    <option value="percent">Tăng theo phần trăm (%)</option>
                                    <option value="fixed">Tăng số tiền cố định (VNĐ)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Giá trị</label>
                                <input type="number" step="0.01" name="adjustment_value" class="form-control" required placeholder="VD: 20">
                            </div>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" checked>
                            <label class="form-check-label fw-bold" for="status">Kích hoạt</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                        <a href="{{ url('/admin/priceSettings') }}" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
