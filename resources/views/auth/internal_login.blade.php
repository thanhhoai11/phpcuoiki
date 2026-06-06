<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h4 class="mb-0">Đăng Nhập Nội Bộ</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ url('/internalauth/login') }}" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên đăng nhập (Username)</label>
                            <input type="text" name="username" class="form-control" required placeholder="Nhập username">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg">Đăng Nhập</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
