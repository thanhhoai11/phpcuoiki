<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class Receptioncontroller extends Controller
{
    public function index()
    {
        // Tạm thời bỏ đoạn kiểm tra đăng nhập để xem giao diện trước

        // Đồng bộ trạng thái phòng dựa trên ngày check-in hôm nay
        try {
            // Reset các phòng sắp nhận (soon_to_checkin) mà hôm nay không có lịch check-in về 'available'
            DB::update("
                UPDATE rooms SET status = 'available' 
                WHERE status = 'soon_to_checkin'
                  AND id NOT IN (
                      SELECT br.room_id 
                      FROM booking_rooms br
                      JOIN bookings b ON b.id = br.booking_id
                      WHERE b.check_in = CURRENT_DATE() 
                        AND b.status NOT IN ('cancelled', 'rejected')
                  )
            ");
            
            // Chuyển các phòng đang trống sang 'soon_to_checkin' nếu có lịch check-in hôm nay
            DB::update("
                UPDATE rooms r
                JOIN booking_rooms br ON r.id = br.room_id
                JOIN bookings b ON b.id = br.booking_id
                SET r.status = 'soon_to_checkin'
                WHERE b.check_in = CURRENT_DATE()
                  AND b.status NOT IN ('cancelled', 'rejected')
                  AND r.status = 'available'
            ");
        } catch (\Exception $e) {
            // Bỏ qua lỗi để tránh lỗi giao diện
        }

        // Fetch all rooms with their types
        $sql = "
            SELECT r.id, r.room_number, r.floor, r.status,
                   rt.type_name, rt.max_guests, rt.price, rt.id as room_type_id
            FROM rooms r
            JOIN room_types rt ON r.room_type_id = rt.id
            ORDER BY r.floor DESC, r.room_number ASC
        ";
        $roomsRaw = DB::select($sql);

        // Group rooms by floor
        $floors = [];
        foreach ($roomsRaw as $r) {
            // Get amenities for this room type
            $amenitiesSql = "
                SELECT a.amenity_name 
                FROM amenities a
                JOIN room_type_amenities rta ON a.id = rta.amenity_id
                WHERE rta.room_type_id = ?
            ";
            $amenities = array_column(DB::select($amenitiesSql, [$r['room_type_id']]), 'amenity_name');
            $r['amenities_list'] = implode(', ', $amenities);

            $floors[$r['floor']][] = $r;
        }

        // Render dashboard
        $this->render('reception/index', ['floors' => $floors], 'dashboard');
    }

    public function updateStatus()
    {
        $roomId = (int)request('room_id');
        $status = request('status');

        $validStatuses = ['available', 'soon_to_checkin', 'occupied', 'soon_to_checkout'];
        if (!in_array($status, $validStatuses)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ: ' . $status]);
            exit;
        }

        try {
            DB::update("UPDATE rooms SET status = ? WHERE id = ?", [$status, $roomId]);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Cập nhật trạng thái phòng thành công!',
                'new_status' => $status
            ]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
            exit;
        }
    }
}
