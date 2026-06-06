<?php

namespace App\Http\Controllers;

use App\Models\PriceSetting;

class Admincontroller extends Controller {

    private PriceSetting $priceSettingModel;

    public function __construct() {
        $this->priceSettingModel = new PriceSetting();
    }

    public function dashboard(): void {
        $this->requireRole('admin');
        $this->render('admin/dashboard', [], 'main');
    }

    public function reports(): void {
        $this->requireRole('admin');
        
        $bookingModel = new \App\Models\Booking();
        $userModel = new \App\Models\User();

        // 1. Nhận bộ lọc
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'room_type_id' => $_GET['room_type_id'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        // 2. Lấy dữ liệu
        $stats = $bookingModel->getReportStats($filters);
        $stats['total_customers'] = $userModel->countCustomers(); // Customer count is independent of booking filters
        
        $chartData = $bookingModel->getReportChartData($filters);
        $bookings = $bookingModel->getFilteredBookings($filters);

        // 3. Lấy danh sách loại phòng cho dropdown
        $roomTypes = \Illuminate\Support\Facades\DB::select('SELECT id, type_name FROM room_types');

        $this->render('admin/reports', [
            'stats' => $stats,
            'chartData' => json_encode($chartData),
            'bookings' => $bookings,
            'filters' => $filters,
            'roomTypes' => $roomTypes
        ], 'main');
    }

    public function priceSettings(): void {
        $this->requireRole('admin');
        $settings = $this->priceSettingModel->getAll();
        $this->render('admin/price_settings/index', ['settings' => $settings], 'main');
    }

    public function priceSettingsCreate(): void {
        $this->requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->priceSettingsStore();
            return;
        }
        $this->render('admin/price_settings/create', [], 'main');
    }

    private function priceSettingsStore(): void {
        $name = trim($_POST['name'] ?? '');
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $type = $_POST['adjustment_type'] ?? 'percent';
        $value = (float)($_POST['adjustment_value'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (!$name || !$start_date || !$end_date || $value <= 0) {
            $this->setFlash('error', 'Vui lòng điền đầy đủ thông tin hợp lệ.');
            $this->redirectToAction('admin', 'priceSettingsCreate');
            return;
        }

        if ($start_date > $end_date) {
            $this->setFlash('error', 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.');
            $this->redirectToAction('admin', 'priceSettingsCreate');
            return;
        }

        if ($this->priceSettingModel->checkOverlap($start_date, $end_date)) {
            $this->setFlash('error', 'Khoảng thời gian này đã bị trùng lặp với một cài đặt giá đang bật. Vui lòng chọn ngày khác.');
            $this->redirectToAction('admin', 'priceSettingsCreate');
            return;
        }

        $this->priceSettingModel->create([
            'name' => $name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'adjustment_type' => $type,
            'adjustment_value' => $value,
            'status' => $status,
        ]);

        $this->setFlash('success', 'Thêm dịp điều chỉnh giá thành công.');
        $this->redirectToAction('admin', 'priceSettings');
    }

    public function priceSettingsEdit(): void {
        $this->requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $setting = $this->priceSettingModel->findById($id);

        if (!$setting) {
            $this->setFlash('error', 'Không tìm thấy cài đặt giá.');
            $this->redirectToAction('admin', 'priceSettings');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->priceSettingsUpdate($id);
            return;
        }

        $this->render('admin/price_settings/edit', ['setting' => $setting], 'main');
    }

    private function priceSettingsUpdate(int $id): void {
        $name = trim($_POST['name'] ?? '');
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $type = $_POST['adjustment_type'] ?? 'percent';
        $value = (float)($_POST['adjustment_value'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (!$name || !$start_date || !$end_date || $value <= 0) {
            $this->setFlash('error', 'Vui lòng điền đầy đủ thông tin hợp lệ.');
            $this->redirectToAction('admin', 'priceSettingsEdit', ['id' => $id]);
            return;
        }

        if ($start_date > $end_date) {
            $this->setFlash('error', 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.');
            $this->redirectToAction('admin', 'priceSettingsEdit', ['id' => $id]);
            return;
        }

        if ($this->priceSettingModel->checkOverlap($start_date, $end_date, $id)) {
            $this->setFlash('error', 'Khoảng thời gian này đã bị trùng lặp với một cài đặt giá đang bật. Vui lòng chọn ngày khác.');
            $this->redirectToAction('admin', 'priceSettingsEdit', ['id' => $id]);
            return;
        }

        $this->priceSettingModel->update($id, [
            'name' => $name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'adjustment_type' => $type,
            'adjustment_value' => $value,
            'status' => $status,
        ]);

        $this->setFlash('success', 'Cập nhật thành công.');
        $this->redirectToAction('admin', 'priceSettings');
    }

    public function priceSettingsDelete(): void {
        $this->requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $this->priceSettingModel->delete($id);
            $this->setFlash('success', 'Đã xóa cài đặt giá.');
        }
        $this->redirectToAction('admin', 'priceSettings');
    }
}
