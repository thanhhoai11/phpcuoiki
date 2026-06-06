<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class BookingService {

    public function createBooking(BookingBuilder $builder, array $roomIds = []): int {
        $data = $builder->build();

        if (empty($roomIds)) {
            $roomIds = [$data['room_id']];
        }

        $totalPrice = 0;
        foreach ($roomIds as $rid) {
            $roomInfo = DB::selectOne(
                'SELECT rt.price
                   FROM rooms r
                   JOIN room_types rt ON rt.id = r.room_type_id
                  WHERE r.id = ?',
                [$rid]
            );
            if (!$roomInfo) {
                throw new \RuntimeException("Ph�ng ID $rid kh�ng t?n t?i.");
            }
            $totalPrice += (float)$roomInfo['price'] * $data['nights'];
        }

        DB::statement('INSERT INTO bookings
               (user_id, customer_name, customer_email, customer_phone,
                check_in, check_out, adult_count, child_count, total_price, payment_method,
                payment_status, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", "pending")',
            [
                $data['user_id']        ?? null,
                $data['customer_name'],
                $data['customer_email'],
                $data['customer_phone'],
                $data['check_in'],
                $data['check_out'],
                $data['people'],
                0,
                $totalPrice,
                null
            ]
        );
        $bookingId = (int)DB::getPdo()->lastInsertId();

        foreach ($roomIds as $rid) {
            DB::statement(
                'INSERT INTO booking_rooms (booking_id, room_id) VALUES (?, ?)',
                [$bookingId, $rid]
            );
            // Chỉ tự động chuyển trạng thái phòng thành 'soon_to_checkin' nếu ngày check-in là HÔM NAY
            if ($data['check_in'] === date('Y-m-d')) {
                DB::statement(
                    'UPDATE rooms SET status = "soon_to_checkin" WHERE id = ?',
                    [$rid]
                );
            }
        }

        return $bookingId;
    }
}

