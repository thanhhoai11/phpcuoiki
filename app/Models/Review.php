<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Review {

    /**
     * Tạo đánh giá mới.
     * 
     * @param array $data ['user_id', 'room_type_id', 'rating', 'comment']
     * @return int ID của đánh giá vừa chèn
     */
    public function create(array $data): int {
        DB::statement(
            "INSERT INTO reviews (user_id, room_type_id, rating, comment, created_at)
             VALUES (?, ?, ?, ?, NOW())",
            [
                (int)$data['user_id'],
                (int)$data['room_type_id'],
                (int)$data['rating'],
                $data['comment'] ?? null
            ]
        );
        return (int)DB::getPdo()->lastInsertId();
    }

    /**
     * Lấy tất cả đánh giá của một loại phòng.
     * 
     * @param int $roomTypeId
     * @return array
     */
    public function findByRoomType(int $roomTypeId): array {
        return DB::select(
            "SELECT r.*, u.fullname, u.username
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.room_type_id = ?
             ORDER BY r.created_at DESC",
            [$roomTypeId]
        );
    }

    /**
     * Lấy điểm đánh giá trung bình và số lượng đánh giá của một loại phòng.
     * 
     * @param int $roomTypeId
     * @return array ['avg_rating', 'count_reviews']
     */
    public function getAverageRating(int $roomTypeId): array {
        $row = DB::selectOne(
            "SELECT AVG(rating) AS avg_rating, COUNT(*) AS count_reviews
             FROM reviews
             WHERE room_type_id = ?",
            [$roomTypeId]
        );
        return [
            'avg_rating'    => $row ? (float)($row['avg_rating'] ?? 0) : 0.0,
            'count_reviews' => $row ? (int)($row['count_reviews'] ?? 0) : 0
        ];
    }
}
