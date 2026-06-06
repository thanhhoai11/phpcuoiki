<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class BookingService {

    public function createBooking(BookingBuilder $builder, array $roomIds = []): int {
        $data = $builder->build();

        if (empty($roomIds)) {
            $roomIds = [$data['room_id']];
        }

        // Lấy tất cả cài đặt giá đang kích hoạt
        $priceSettings = DB::select("SELECT * FROM price_settings WHERE status = 1");

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
                throw new \RuntimeException("Phòng ID $rid không tồn tại.");
            }
            
            // Xử lý type cast cho stdClass hoặc array tùy DB driver
            $basePrice = is_array($roomInfo) ? (float)$roomInfo['price'] : (float)$roomInfo->price;
            $roomTotal = 0;

            // Tính tiền từng đêm
            $checkInDate = new \DateTime($data['check_in']);
            $checkOutDate = new \DateTime($data['check_out']);
            
            $currentDate = clone $checkInDate;
            while ($currentDate < $checkOutDate) {
                $dateString = $currentDate->format('Y-m-d');
                $nightPrice = $basePrice;

                // Áp dụng phụ thu nếu ngày hiện tại nằm trong dịp lễ
                foreach ($priceSettings as $setting) {
                    $sd = is_array($setting) ? $setting['start_date'] : $setting->start_date;
                    $ed = is_array($setting) ? $setting['end_date'] : $setting->end_date;
                    $at = is_array($setting) ? $setting['adjustment_type'] : $setting->adjustment_type;
                    $av = is_array($setting) ? $setting['adjustment_value'] : $setting->adjustment_value;

                    if ($dateString >= $sd && $dateString <= $ed) {
                        if ($at === 'percent') {
                            $nightPrice += $basePrice * ((float)$av / 100);
                        } else {
                            $nightPrice += (float)$av;
                        }
                        break; // Chỉ áp dụng 1 mức phụ thu (đã chặn trùng lặp ở Controller)
                    }
                }

                $roomTotal += $nightPrice;
                $currentDate->modify('+1 day');
            }

            $totalPrice += $roomTotal;
        }

        DB::statement('INSERT INTO bookings
               (user_id, customer_name, customer_email, customer_phone,
                check_in, check_out, people, total_price, payment_method,
                payment_status, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", "pending")',
            [
                $data['user_id']        ?? null,
                $data['customer_name'],
                $data['customer_email'],
                $data['customer_phone'],
                $data['check_in'],
                $data['check_out'],
                $data['people'],
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
        }

        return $bookingId;
    }
}
