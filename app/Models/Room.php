<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * app/models/Room.php
 *
 * Khớp với CSDL:
 *   rooms      (id, room_number, room_type_id, floor)
 *   room_types (id, type_name, price, max_guests, description)
 *   amenities  (id, amenity_name)
 *   room_type_amenities (room_type_id, amenity_id)
 */
class Room {

    

    

    // ─────────────────────────────────────────────
    // PHÒNG + LOẠI PHÒNG (JOIN)
    // ─────────────────────────────────────────────

    public function all(): array {
        return DB::select(
            'SELECT r.id, r.room_number, r.floor,
                    rt.id AS room_type_id, rt.type_name, rt.price,
                    rt.max_guests, rt.description
               FROM rooms r
               JOIN room_types rt ON rt.id = r.room_type_id
              ORDER BY r.floor, r.room_number'
        );
    }

    public function findById(int $id): ?array {
        $room = DB::selectOne(
            'SELECT r.id, r.room_number, r.floor,
                    rt.id AS room_type_id, rt.type_name, rt.price,
                    rt.max_guests, rt.description
               FROM rooms r
               JOIN room_types rt ON rt.id = r.room_type_id
              WHERE r.id = ?',
            [$id]
        );

        if ($room) {
            $room['amenities'] = $this->getAmenitiesByType($room['room_type_id']);
        }

        return $room;
    }

    public function allTypes(): array {
        return DB::select('SELECT * FROM room_types ORDER BY price');
    }

    public function findTypeById(int $typeId): ?array {
        $type = DB::selectOne('SELECT * FROM room_types WHERE id = ?', [$typeId]);
        if ($type) {
            $type['amenities'] = $this->getAmenitiesByType($typeId);
        }
        return $type;
    }

    public function getAmenitiesByType(int $roomTypeId): array {
        return DB::select(
            'SELECT a.id, a.amenity_name
               FROM amenities a
               JOIN room_type_amenities rta ON rta.amenity_id = a.id
              WHERE rta.room_type_id = ?
              ORDER BY a.id',
            [$roomTypeId]
        );
    }

    /**
     * Lấy toàn bộ tiện nghi trong hệ thống (dùng cho bộ lọc tìm kiếm).
     */
    public function getAllAmenities(): array {
        return DB::select(
            'SELECT id, amenity_name FROM amenities ORDER BY amenity_name'
        );
    }
    public function getRoomsByType(int $typeId, string $checkIn = '', string $checkOut = ''): array {
        $rooms = DB::select(
            'SELECT id, room_number, floor
               FROM rooms
              WHERE room_type_id = ?
              ORDER BY floor, room_number',
            [$typeId]
        );

        if ($checkIn && $checkOut) {
            // Lấy danh sách room_id đã bị đặt trong khoảng ngày này
            $booked = DB::select(
                'SELECT DISTINCT br.room_id
                   FROM booking_rooms br
                   JOIN bookings b ON b.id = br.booking_id
                  WHERE b.status NOT IN ("cancelled","rejected")
                    AND b.check_in  < ?
                    AND b.check_out > ?',
                [$checkOut, $checkIn]
            );
            $bookedIds = array_map('intval', array_column($booked, 'room_id'));

            foreach ($rooms as &$room) {
                $room['is_booked'] = in_array((int)$room['id'], $bookedIds, true);
            }
            unset($room);
        } else {
            foreach ($rooms as &$room) {
                $room['is_booked'] = false;
            }
            unset($room);
        }

        return $rooms;
    }

    // ─────────────────────────────────────────────
    // TÌM PHÒNG TRỐNG
    // ─────────────────────────────────────────────

    public function searchAvailable(string $checkIn, string $checkOut, int $guests = 1): array {
        return DB::select(
            'SELECT r.id, r.room_number, r.floor,
                    rt.id AS room_type_id, rt.type_name, rt.price,
                    rt.max_guests, rt.description
               FROM rooms r
               JOIN room_types rt ON rt.id = r.room_type_id
              WHERE rt.max_guests >= ?
                AND r.id NOT IN (
                    SELECT br.room_id
                      FROM booking_rooms br
                      JOIN bookings b ON b.id = br.booking_id
                     WHERE b.status NOT IN ("cancelled","rejected")
                       AND b.check_in  < ?
                       AND b.check_out > ?
                )
              ORDER BY rt.price, r.room_number',
            [$guests, $checkOut, $checkIn]
        );
    }

    /**
     * Tìm phòng còn trống, trả về kết quả GROUP BY loại phòng.
     * Mỗi phần tử trong mảng kết quả là 1 loại phòng, bên trong có key 'available_rooms'
     * chứa danh sách các phòng cụ thể còn trống.
     *
     * Cấu trúc trả về:
     * [
     *   [
     *     'room_type_id' => 1,
     *     'type_name'    => 'Phòng Đơn Tiêu Chuẩn',
     *     'price'        => 400000,
     *     'max_guests'   => 1,
     *     'description'  => '...',
     *     'amenities'    => [...],
     *     'available_rooms' => [
     *       ['id'=>1, 'room_number'=>'101', 'floor'=>1],
     *       ['id'=>2, 'room_number'=>'102', 'floor'=>1],
     *     ]
     *   ],
     *   ...
     * ]
     */
    public function searchAvailableGroupedByType(string $checkIn, string $checkOut, int $guests = 1): array {
        // 1. Lấy tất cả loại phòng thỏa mãn sức chứa
        $types = DB::select(
            'SELECT * FROM room_types ORDER BY price',
            []
        );

        // 2. Lấy danh sách ID phòng đã bị đặt trong khoảng ngày này
        $booked = DB::select(
            'SELECT DISTINCT br.room_id
               FROM booking_rooms br
               JOIN bookings b ON b.id = br.booking_id
              WHERE b.status NOT IN ("cancelled","rejected")
                AND b.check_in  < ?
                AND b.check_out > ?',
            [$checkOut, $checkIn]
        );
        $bookedIds = array_map('intval', array_column($booked, 'room_id'));

        $result = [];
        foreach ($types as $type) {
            $typeId = $type['id'];
            
            // 3. Lấy TẤT CẢ phòng của loại này
            $allRooms = DB::select(
                'SELECT id, room_number, floor FROM rooms WHERE room_type_id = ? ORDER BY floor, room_number',
                [$typeId]
            );

            $roomsWithStatus = [];
            foreach ($allRooms as $r) {
                $r['is_booked'] = in_array((int)$r['id'], $bookedIds, true);
                $roomsWithStatus[] = $r;
            }

            // Chỉ thêm loại phòng vào kết quả nếu nó có ít nhất 1 phòng (trống hoặc đã đặt)
            if (!empty($roomsWithStatus)) {
                $type['room_type_id'] = $typeId; // Để tương thích với view
                $type['amenities']    = $this->getAmenitiesByType($typeId);
                $type['all_rooms']    = $roomsWithStatus;
                
                // Giữ lại available_rooms cho các logic cũ nếu cần
                $type['available_rooms'] = array_values(array_filter($roomsWithStatus, fn($r) => !$r['is_booked']));
                
                $result[] = $type;
            }
        }

        return $result;
    }

    public function isAvailable(int $roomId, string $checkIn, string $checkOut): bool {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS cnt
               FROM booking_rooms br
               JOIN bookings b ON b.id = br.booking_id
              WHERE br.room_id = ?
                AND b.status NOT IN ("cancelled","rejected")
                AND b.check_in  < ?
                AND b.check_out > ?',
            [$roomId, $checkOut, $checkIn]
        );
        return ($result['cnt'] ?? 1) == 0;
    }

    // ─────────────────────────────────────────────
    // THỐNG KÊ
    // ─────────────────────────────────────────────

    public function countAll(): int {
        $r = DB::selectOne('SELECT COUNT(*) AS cnt FROM rooms');
        return (int)($r['cnt'] ?? 0);
    }
}

