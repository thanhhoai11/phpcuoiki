<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * app/models/User.php
 */
class User {
    

    

    public function findByEmail(string $email): ?array {
        return DB::selectOne("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public function findById(int $id): ?array {
        return DB::selectOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function findByUsername(string $username): ?array {
        return DB::selectOne("SELECT * FROM users WHERE username = ?", [$username]);
    }

    public function create(array $data): int {
        DB::statement("INSERT INTO users (username, fullname, email, phone, password, verified, otp_code, created_at)
                VALUES (?, ?, ?, ?, ?, 0, ?, NOW())",
                [
                    $data['username'] ?? '',
                    $data['fullname'] ?? '',
                    $data['email']    ?? '',
                    $data['phone']    ?? '',
                    $data['password'] ?? '',
                    $data['otp_code'] ?? null,
                ]
        );
        return (int)DB::getPdo()->lastInsertId();
    }

    public function verifyAccount(string $email, string $otp): bool {
        $user = DB::selectOne("SELECT * FROM users WHERE email = ? AND otp_code = ?", [$email, $otp]);
        if ($user) {
            DB::statement("UPDATE users SET verified = 1, otp_code = NULL WHERE id = ?", [$user['id']]);
            return true;
        }
        return false;
    }

    public function updateOtp(string $email, string $otp): int {
        return DB::statement("UPDATE users SET otp_code = ? WHERE email = ?", [$otp, $email]);
    }

    public function updatePassword(string $email, string $hashedPassword): int {
        return DB::statement("UPDATE users SET password = ? WHERE email = ?", [$hashedPassword, $email]);
    }

    // --- OTP & Password Resets ---

    public function deleteResetToken(string $email): int {
        return DB::statement("DELETE FROM password_resets WHERE email = ?", [$email]);
    }

    public function saveResetToken(string $email, string $token): int {
        return DB::statement(
            "INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())",
            [$email, $token]
        );
    }

    public function findResetToken(string $email, string $token): ?array {
        return DB::selectOne(
            "SELECT * FROM password_resets WHERE email = ? AND token = ?", 
            [$email, $token]
        );
    }
}



