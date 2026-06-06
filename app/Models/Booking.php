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
        return true;
    }

    public function countByStatus(string $status): int {
        $r = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM bookings WHERE status = ?', [$status]
        );
        return (int)($r['cnt'] ?? 0);
    }

    public function countAll(): int {
        $r = DB::selectOne('SELECT COUNT(*) AS cnt FROM bookings');
        return (int)($r['cnt'] ?? 0);
    }

    public function totalRevenue(): float {
        $r = DB::selectOne(
            "SELECT SUM(total_price) AS total FROM bookings WHERE payment_status = 'paid'"
        );
        return (float)($r['total'] ?? 0);
    }

    // --- BÁO CÁO NÂNG CAO ---

    private function buildReportConditions(array $filters): array {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['start_date'])) {
            $where[] = "b.check_in >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "b.check_in <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['status'])) {
            $where[] = "b.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['room_type_id'])) {
            $where[] = "b.id IN (SELECT br.booking_id FROM booking_rooms br JOIN rooms r ON br.room_id = r.id WHERE r.room_type_id = ?)";
            $params[] = $filters['room_type_id'];
        }

        $whereSql = implode(' AND ', $where);
        return [$whereSql, $params];
    }

    public function getReportStats(array $filters): array {
        list($whereSql, $params) = $this->buildReportConditions($filters);

        $sql = "SELECT 
                    SUM(CASE WHEN b.payment_status = 'paid' AND b.status != 'cancelled' THEN b.total_price ELSE 0 END) AS total_revenue,
                    COUNT(*) AS total_bookings,
                    SUM(DATEDIFF(b.check_out, b.check_in)) AS total_nights,
                    SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                    SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count,
                    SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count
                FROM bookings b
                WHERE $whereSql";
        
        $r = DB::selectOne($sql, $params);
        
        $totalBookings = (int)($r['total_bookings'] ?? 0);
        $totalRevenue = (float)($r['total_revenue'] ?? 0);
        $totalNights = (int)($r['total_nights'] ?? 0);
        $cancelledCount = (int)($r['cancelled_count'] ?? 0);
        
        $avgRevenue = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;
        $cancelRate = $totalBookings > 0 ? ($cancelledCount / $totalBookings) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_bookings' => $totalBookings,
            'total_nights' => $totalNights,
            'pending_count' => (int)($r['pending_count'] ?? 0),
            'confirmed_count' => (int)($r['confirmed_count'] ?? 0),
            'cancelled_count' => $cancelledCount,
            'avg_revenue' => $avgRevenue,
            'cancel_rate' => $cancelRate
        ];
    }

    public function getReportChartData(array $filters): array {
        list($whereSql, $params) = $this->buildReportConditions($filters);
        
        $sql = "SELECT b.check_in AS date, SUM(b.total_price) AS revenue
                FROM bookings b
                WHERE $whereSql AND b.payment_status = 'paid' AND b.status != 'cancelled'
                GROUP BY b.check_in
                ORDER BY b.check_in ASC";
        
        return DB::select($sql, $params);
    }

    public function getFilteredBookings(array $filters): array {
        list($whereSql, $params) = $this->buildReportConditions($filters);
        
        $sql = "SELECT b.*,
                       (SELECT GROUP_CONCAT(r.room_number ORDER BY r.room_number SEPARATOR ', ')
                          FROM booking_rooms br2 
                          JOIN rooms r ON br2.room_id = r.id 
                         WHERE br2.booking_id = b.id) AS room_number,
                       (SELECT GROUP_CONCAT(rt.type_name SEPARATOR ', ')
                          FROM booking_rooms br3 
                          JOIN rooms r2 ON br3.room_id = r2.id 
                          JOIN room_types rt ON r2.room_type_id = rt.id 
                         WHERE br3.booking_id = b.id) AS type_name
                  FROM bookings b
                 WHERE $whereSql
                 ORDER BY b.check_in DESC";
                 
        return DB::select($sql, $params);
    }
}



