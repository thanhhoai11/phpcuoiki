<?php

namespace App\Http\Controllers;

/**
 * app/controllers/Authcontroller.php
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require PHPMailer from the external vendor folder to avoid workspace copy issues

class Authcontroller extends Controller {

    private \App\Models\User $userModel;

    public function __construct() {
        $this->userModel = new \App\Models\User();
    }

    // ── ĐĂNG NHẬP ──────────────────────────────────
    public function login(): void {
        // Nếu đã đăng nhập, về trang chủ
        if (!empty($_SESSION['user_id'])) {
            $this->redirectToAction('home', 'index');
        }

        // Lưu flag redirect nếu được gửi từ BookingController (giống login.php của PHP_KT2-master)
        if (isset($_GET['redirect'])) {
            $_SESSION['redirect'] = $_GET['redirect'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->setFlash('error', 'Vui lòng nhập đầy đủ thông tin!');
            } else {
                $user = $this->userModel->findByEmail($email);
                if ($user) {
                    // Kiểm tra xác thực OTP
                    if ($user['verified'] == 0) {
                        $_SESSION['verify_email'] = $email;
                        $this->setFlash('error', 'Tài khoản của bạn chưa được xác thực. Vui lòng nhập mã OTP để kích hoạt!');
                        $this->redirectToAction('auth', 'verifyAccount');
                        return;
                    }

                    $validPassword = false;
                    // Hỗ trợ cả 3 định dạng mật khẩu như file cũ (Bcrypt, MD5, Plaintext)
                    if (password_verify($password, $user['password'])) {
                        $validPassword = true;
                    } elseif (md5($password) === $user['password']) {
                        $validPassword = true;
                    } elseif ($password === $user['password']) {
                        $validPassword = true;
                    }

                    if ($validPassword) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user'] = $user;
                        $this->setFlash('success', 'Đăng nhập thành công!');

                        // Ưu tiên quay lại luồng đặt phòng nếu có cờ đánh dấu
                        if (!empty($_SESSION['redirect_to_booking'])) {
                            unset($_SESSION['redirect_to_booking']);
                            $_SESSION['restore_booking'] = true; // Cờ dự phòng cho BookingController
                            $this->redirectToAction('booking', 'create', ['restore' => '1']);
                            return;
                        }

                        $redirect = $_SESSION['redirect_after_login'] ?? null;
                        unset($_SESSION['redirect_after_login']);
                        if ($redirect) {
                            header('Location: ' . $redirect);
                            exit;
                        }
                        $this->redirectToAction('home', 'index');
                        return;
                    } else {
                        $this->setFlash('error', 'Sai mật khẩu!');
                    }
                } else {
                    $this->setFlash('error', 'Tài khoản không tồn tại!');
                }
            }
            // Nếu lỗi, load lại view với flash message
        }

        // GET request hoặc lỗi đăng nhập
        $this->render('auth/login', [], 'auth');
    }

    // ── ĐĂNG KÝ ────────────────────────────────────
    public function register(): void {
        if (!empty($_SESSION['user_id'])) {
            $this->redirectToAction('home', 'index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                $this->setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
                $_SESSION['old'] = compact('name', 'email', 'phone');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Email không hợp lệ.');
                $_SESSION['old'] = compact('name', 'email', 'phone');
            } elseif (strlen($password) < 6) {
                $this->setFlash('error', 'Mật khẩu phải từ 6 ký tự trở lên.');
                $_SESSION['old'] = compact('name', 'email', 'phone');
            } else {
                // Kiểm tra email trùng
                $existingEmail = $this->userModel->findByEmail($email);
                if ($existingEmail) {
                    $this->setFlash('error', 'Email đã được sử dụng. Vui lòng chọn email khác.');
                    $_SESSION['old'] = compact('name', 'phone');
                } else 
                {
                        // Hash mật khẩu
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Tạo mã OTP đăng ký (6 chữ số)
                        $otp = sprintf("%06d", mt_rand(1, 999999));

                        try {
                            $this->userModel->create([
                                'username' => $name,
                                'fullname' => $name,
                                'email'    => $email,
                                'phone'    => $phone,
                                'password' => $hashedPassword,
                                'otp_code' => $otp
                            ]);

                            if ($this->sendOtpEmail($email, $otp, 'verify_account')) {
                                $_SESSION['verify_email'] = $email;
                                $this->setFlash('success', 'Đăng ký thành công! Vui lòng kiểm tra email để lấy mã OTP xác thực tài khoản.');
                                $this->redirectToAction('auth', 'verifyAccount');
                                return;
                            } else {
                                $this->setFlash('warning', 'Đăng ký thành công nhưng không thể gửi email OTP. Vui lòng liên hệ hỗ trợ.');
                                $this->redirectToAction('auth', 'login');
                                return;
                            }
                        } catch (\Throwable $e) {
                            $this->setFlash('error', 'Lỗi hệ thống: ' . $e->getMessage());
                            $_SESSION['old'] = compact('name', 'email', 'phone');
                        }
                    
                }
            }
        }

        $this->render('auth/register', [], 'auth');
    }

    // ── ĐĂNG XUẤT ──────────────────────────────────
    public function logout(): void {
        session_destroy();
        session_start();
        $this->setFlash('success', 'Bạn đã đăng xuất.');
        $this->redirectToAction('home', 'index');
    }

    // ── QUÊN MẬT KHẨU ──────────────────────────────
    public function forgotPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email)) {
                $this->setFlash('error', 'Vui lòng nhập địa chỉ email!');
            } else {
                $user = $this->userModel->findByEmail($email);
                if ($user) {
                    $otp = sprintf("%06d", mt_rand(1, 999999));
                    
                    $this->userModel->deleteResetToken($email);
                    $this->userModel->saveResetToken($email, $otp);

                    if ($this->sendOtpEmail($email, $otp)) {
                        $_SESSION['reset_email'] = $email;
                        $this->setFlash('success', 'Mã OTP đã được gửi đến email của bạn.');
                        $this->redirectToAction('auth', 'verifyResetOtp');
                        return;
                    } else {
                        $this->setFlash('error', 'Không thể gửi email OTP. Vui lòng thử lại sau.');
                    }
                } else {
                    // Để bảo mật, không nên báo lỗi "Email không tồn tại"
                    $this->setFlash('error', 'Email không tồn tại trong hệ thống!');
                }
            }
        }

        $this->render('auth/forgot_password', [], 'auth');
    }

    // ── GỬI LẠI OTP QUÊN MẬT KHẨU ────────────────
    public function resendResetOtp(): void {
        if (!isset($_SESSION['reset_email'])) {
            $this->redirectToAction('auth', 'forgotPassword');
        }
        
        // Giới hạn 1 phút
        $lastSent = $_SESSION['last_otp_sent'] ?? 0;
        if (time() - $lastSent < 60) {
            $seconds = 60 - (time() - $lastSent);
            $this->setFlash('error', "Vui lòng đợi {$seconds} giây nữa mới được gửi lại mã.");
            $this->redirectToAction('auth', 'verifyResetOtp');
            return;
        }

        $email = $_SESSION['reset_email'];
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            $otp = sprintf("%06d", mt_rand(1, 999999));
            $this->userModel->deleteResetToken($email);
            $this->userModel->saveResetToken($email, $otp);

            if ($this->sendOtpEmail($email, $otp)) {
                $_SESSION['last_otp_sent'] = time();
                $this->setFlash('success', 'Mã OTP mới đã được gửi. Kiểm tra hộp thư của bạn.');
            } else {
                $this->setFlash('error', 'Lỗi gửi email. Vui lòng thử lại.');
            }
        }
        $this->redirectToAction('auth', 'verifyResetOtp');
    }

    // ── XÁC THỰC OTP ───────────────────────────────
    public function verifyResetOtp(): void {
        if (!isset($_SESSION['reset_email'])) {
            $this->redirectToAction('auth', 'forgotPassword');
        }

        $email = $_SESSION['reset_email'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otp = trim($_POST['otp'] ?? '');
            if (empty($otp)) {
                $this->setFlash('error', 'Vui lòng nhập mã OTP!');
            } else {
                $validToken = $this->userModel->findResetToken($email, $otp);
                if ($validToken) {
                    $_SESSION['otp_verified'] = true;
                    $this->redirectToAction('auth', 'resetPassword');
                    return;
                } else {
                    $this->setFlash('error', 'Mã OTP không đúng hoặc đã hết hạn.');
                }
            }
        }

        $this->render('auth/verify_reset_otp', ['email' => $email], 'auth');
    }

    // ── ĐẶT LẠI MẬT KHẨU ───────────────────────────
    public function resetPassword(): void {
        if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])) {
            $this->redirectToAction('auth', 'forgotPassword');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($newPassword) || empty($confirmPassword)) {
                $this->setFlash('error', 'Vui lòng điền đủ mật khẩu!');
            } elseif ($newPassword !== $confirmPassword) {
                $this->setFlash('error', 'Mật khẩu xác nhận không khớp!');
            } elseif (strlen($newPassword) < 6) {
                $this->setFlash('error', 'Mật khẩu phải có ít nhất 6 ký tự!');
            } else {
                $email = $_SESSION['reset_email'];
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                try {
                    $this->userModel->updatePassword($email, $hashedPassword);
                    $this->userModel->deleteResetToken($email);
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['otp_verified']);
                    
                    $this->setFlash('success', 'Mật khẩu đã được thay đổi thành công! Vui lòng đăng nhập.');
                    $this->redirectToAction('auth', 'login');
                    return;
                } catch (\Throwable $e) {
                    $this->setFlash('error', 'Có lỗi hệ thống khi cập nhật mật khẩu: ' . $e->getMessage());
                }
            }
        }

        $this->render('auth/reset_password', [], 'auth');
    }

    // ── XÁC THỰC TÀI KHOẢN (ĐĂNG KÝ) ──────────────
    public function verifyAccount(): void {
        if (!isset($_SESSION['verify_email'])) {
            $this->redirectToAction('auth', 'register');
        }

        $email = $_SESSION['verify_email'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otp = trim($_POST['otp'] ?? '');
            if (empty($otp)) {
                $this->setFlash('error', 'Vui lòng nhập mã OTP!');
            } else {
                if ($this->userModel->verifyAccount($email, $otp)) {
                    unset($_SESSION['verify_email']);
                    $this->setFlash('success', 'Xác thực tài khoản thành công! Bây giờ bạn có thể đăng nhập.');
                    $this->redirectToAction('auth', 'login');
                    return;
                } else {
                    $this->setFlash('error', 'Mã OTP không chính xác hoặc đã hết hạn.');
                }
            }
        }

        $this->render('auth/verify_account', ['email' => $email], 'auth');
    }

    // ── GỬI LẠI OTP ĐĂNG KÝ ────────────────────────
    public function resendVerifyOtp(): void {
        if (!isset($_SESSION['verify_email'])) {
            $this->redirectToAction('auth', 'register');
        }
        
        // Giới hạn 1 phút
        $lastSent = $_SESSION['last_otp_sent'] ?? 0;
        if (time() - $lastSent < 60) {
            $seconds = 60 - (time() - $lastSent);
            $this->setFlash('error', "Vui lòng đợi {$seconds} giây nữa mới được gửi lại mã.");
            $this->redirectToAction('auth', 'verifyAccount');
            return;
        }

        $email = $_SESSION['verify_email'];
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Cập nhật mã OTP mới vào DB (không quan trọng affected rows là bao nhiêu)
        $this->userModel->updateOtp($email, $otp);

        if ($this->sendOtpEmail($email, $otp, 'verify_account')) {
            $_SESSION['last_otp_sent'] = time();
            $this->setFlash('success', 'Mã OTP mới đã được gửi thành công. Vui lòng kiểm tra email của bạn.');
        } else {
            $this->setFlash('error', 'Không thể gửi email lúc này. Vui lòng kiểm tra lại cấu hình SMTP hoặc thử lại sau.');
        }
        $this->redirectToAction('auth', 'verifyAccount');
    }

    // ── HÀM TRỢ GIÚP: GỬI EMAIL OTP ────────────────
    private function sendOtpEmail(string $email, string $otp, string $type = 'reset_password'): bool {
        $cfg = require ROOT_PATH . '/config/config.php';
        $mailCfg = $cfg['mail'];
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $mailCfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailCfg['username']; 
            $mail->Password   = $mailCfg['password']; 
            $mail->SMTPSecure = $mailCfg['encryption'] ?? 'tls';
            $mail->Port       = $mailCfg['port'] ?? 587;

            $mail->setFrom($mailCfg['from_email'], $mailCfg['from_name']);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            
            if ($type === 'verify_account') {
                $mail->Subject = 'Xác thực tài khoản - ' . $mailCfg['from_name'];
                $title = 'Xác thực tài khoản';
                $desc = 'Chào mừng bạn đến với ' . $mailCfg['from_name'] . '. Vui lòng sử dụng mã OTP dưới đây để xác thực tài khoản của bạn:';
            } else {
                $mail->Subject = 'Khôi phục mật khẩu - ' . $mailCfg['from_name'];
                $title = 'Khôi phục mật khẩu';
                $desc = 'Bạn vừa yêu cầu khôi phục mật khẩu tài khoản tại ' . $mailCfg['from_name'] . '. Mã OTP của bạn là:';
            }

            $mail->Body    = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                    <h2 style='color: #1e3a8a; text-align: center; margin-top: 0;'>$title</h2>
                    <p style='color: #4b5563; line-height: 1.6;'>$desc</p>
                    <div style='background-color: #eff6ff; padding: 20px; text-align: center; border-radius: 8px; margin: 25px 0; border: 1px dashed #1d4ed8;'>
                        <strong style='font-size: 36px; color: #1d4ed8; letter-spacing: 8px;'>$otp</strong>
                    </div>
                    <p style='color: #6b7280; font-size: 14px;'>Mã này có hiệu lực trong 10 phút. Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.</p>
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;'>
                    <p style='color: #9ca3af; font-size: 12px; text-align: center; margin-bottom: 0;'>
                        Trân trọng,<br><strong>" . $mailCfg['from_name'] . " Team</strong>
                    </p>
                </div>
            </body>
            </html>
            ";

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            if (isset($mail)) {
                error_log("Mailer Info: " . $mail->ErrorInfo);
            }
            return false;
        }
    }
}


