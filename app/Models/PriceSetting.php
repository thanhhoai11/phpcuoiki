<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class PriceSetting {
    
    public function getAll(): array {
        return DB::select("SELECT * FROM price_settings ORDER BY start_date DESC");
    }

    public function getActive(): array {
        return DB::select("SELECT * FROM price_settings WHERE status = 1 ORDER BY start_date ASC");
    }

    public function findById(int $id): ?array {
        return DB::selectOne("SELECT * FROM price_settings WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        DB::statement("INSERT INTO price_settings (name, start_date, end_date, adjustment_type, adjustment_value, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['name'],
                $data['start_date'],
                $data['end_date'],
                $data['adjustment_type'],
                $data['adjustment_value'],
                $data['status'],
            ]
        );
        return (int)DB::getPdo()->lastInsertId();
    }

    public function update(int $id, array $data): int {
        return DB::statement("UPDATE price_settings SET name = ?, start_date = ?, end_date = ?, adjustment_type = ?, adjustment_value = ?, status = ?, updated_at = NOW() WHERE id = ?",
            [
                $data['name'],
                $data['start_date'],
                $data['end_date'],
                $data['adjustment_type'],
                $data['adjustment_value'],
                $data['status'],
                $id
            ]
        );
    }

    public function delete(int $id): int {
        return DB::statement("DELETE FROM price_settings WHERE id = ?", [$id]);
    }

    public function checkOverlap(string $startDate, string $endDate, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM price_settings 
                  WHERE status = 1 
                  AND (
                      (start_date <= ? AND end_date >= ?) OR
                      (start_date <= ? AND end_date >= ?) OR
                      (start_date >= ? AND end_date <= ?)
                  )";
        $params = [$startDate, $startDate, $endDate, $endDate, $startDate, $endDate];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = DB::selectOne($query, $params);
        return $result['count'] > 0;
    }
}
