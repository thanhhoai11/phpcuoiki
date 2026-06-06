<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Booking {

    

    

    /**
     * Chi tiết 1 booking.
     * Nếu booking có nhiều phòng → chỉ trả về hàng đầu tiên (JOIN LEFT).
     * Để lấy tất cả phòng, dùng findRoomsByBooking().
     */
    public function findById(int $id): ?array {
        return DB::selectOne(
            'SELECT b.*,
                    r.room_number, r.floor,
                    rt.type_name, rt.price AS room_price
               FROM bookings b
          LEFT JOIN booking_rooms br ON br.booking_id = b.id
          LEFT JOIN rooms r          ON r.id = br.room_id
          LEFT JOIN room_types rt    ON rt.id = r.room_type_id
              WHERE b.id = ?',
            [$id]
        );
    }

    /**
     * Lấy tất cả phòng của 1 booking (dùng cho success page nhiều phòng).
     */
    public function findRoomsByBooking(int $bookingId): array {
        return DB::select(
            'SELECT r.id, r.room_number, r.floor,
                    rt.type_name, rt.price
               FROM booking_rooms br
               JOIN rooms r       ON r.id  = br.room_id
               JOIN room_types rt ON rt.id = r.room_type_id
              WHERE br.booking_id = ?',
            [$bookingId]
        );
    }

    public function findByUser(int $userId): array {
        return DB::select(
            'SELECT b.*,
                    GROUP_CONCAT(r.room_number ORDER BY r.room_number SEPARATOR ", ") AS room_number,
                    rt.type_name
               FROM bookings b
          LEFT JOIN booking_rooms br ON br.booking_id = b.id
          LEFT JOIN rooms r          ON r.id = br.room_id
          LEFT JOIN room_types rt    ON rt.id = r.room_type_id
              WHERE b.user_id = ?
              GROUP BY b.id
              ORDER BY b.booking_date DESC',
            [$userId]
        );
    }

    public function all(): array {
        return DB::select(
            'SELECT b.*,
                    GROUP_CONCAT(r.room_number ORDER BY r.room_number SEPARATOR ", ") AS room_number,
                    rt.type_name
               FROM bookings b
          LEFT JOIN booking_rooms br ON br.booking_id = b.id
          LEFT JOIN rooms r          ON r.id = br.room_id
          LEFT JOIN room_types rt    ON rt.id = r.room_type_id
              GROUP BY b.id
              ORDER BY b.booking_date DESC'
        );
    }

    public function updateStatus(int $id, string $status): void {
        DB::statement('UPDATE bookings SET status = ? WHERE id = ?', [$status, $id]);
        
        // Neu huy/tu choi booking, tu dong tra trang thai phong ve "available"
        if (in_array($status, ['cancelled', 'rejected'])) {
            $rooms = DB::select('SELECT room_id FROM booking_rooms WHERE booking_id = ?', [$id]);
            foreach ($rooms as $r) {
                DB::statement('UPDATE rooms SET status = "available" WHERE id = ?', [$r['room_id']]);
            }
        }
    }

    public function updatePaymentStatus(int $id, string $method, string $status = 'paid'): void {
        DB::statement(
            'UPDATE bookings SET payment_method = ?, payment_status = ? WHERE id = ?',
            [$method, $status, $id]
        );
    }

    public function cancel(int $id, int $userId): bool {
        $booking = $this->findById($id);
        if (!$booking || (int)$booking['user_id'] !== $userId) return false;
        if ($booking['status'] !== 'pending') return false;

        DB::statement('UPDATE bookings SET status = "cancelled" WHERE id = ?', [$id]);
        
        // Giai phong phong ve "available" khi huy dat
        $rooms = DB::select('SELECT room_id FROM booking_rooms WHERE booking_id = ?', [$id]);
        foreach ($rooms as $r) {
            DB::statement('UPDATE rooms SET status = "available" WHERE id = ?', [$r['room_id']]);
        }
        return true;
    }

    public function countByStatus(string $status): int {
        $r = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM bookings WHERE status = ?', [$status]
        );
        return (int)($r['cnt'] ?? 0);
    }

    public function totalRevenue(): float {
        $r = DB::selectOne(
            "SELECT SUM(total_price) AS total FROM bookings WHERE payment_status = 'paid'"
        );
        return (float)($r['total'] ?? 0);
    }
}



