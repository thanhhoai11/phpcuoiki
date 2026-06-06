<?php

namespace App\Http\Controllers;

class Internalauthcontroller extends Controller {

    private \App\Models\User $userModel;

    public function __construct() {
        $this->userModel = new \App\Models\User();
    }

    public function login(): void {
        $user = $this->currentUser();
        if ($user) {
            if (($user['role'] ?? 'customer') === 'admin') {
                $this->redirectToAction('admin', 'dashboard');
                return;
            } elseif (($user['role'] ?? 'customer') === 'receptionist') {
                $this->redirectToAction('receptionist', 'dashboard');
                return;
            }
            $this->redirectToAction('home', 'index');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $this->setFlash('error', 'Vui lòng nhập đầy đủ username và mật khẩu!');
            } else {
                $user = $this->userModel->findByUsername($username);
                if ($user) {
                    $validPassword = false;
                    if (password_verify($password, $user['password'])) {
                        $validPassword = true;
                    } elseif (md5($password) === $user['password']) {
                        $validPassword = true;
                    } elseif ($password === $user['password']) {
                        $validPassword = true;
                    }

                    if ($validPassword) {
                        $role = $user['role'] ?? 'customer';
                        if ($role === 'customer') {
                            $this->setFlash('error', 'Tài khoản khách hàng không được phép đăng nhập tại đây!');
                        } else {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user'] = $user;
                            $this->setFlash('success', 'Đăng nhập thành công!');

                            if ($role === 'admin') {
                                $this->redirectToAction('admin', 'dashboard');
                                return;
                            } elseif ($role === 'receptionist') {
                                $this->redirectToAction('receptionist', 'dashboard');
                                return;
                            }
                        }
                    } else {
                        $this->setFlash('error', 'Sai mật khẩu!');
                    }
                } else {
                    $this->setFlash('error', 'Tài khoản không tồn tại!');
                }
            }
        }

        $this->render('auth/internal_login', [], 'auth');
    }
    
    public function logout(): void {
        session_destroy();
        session_start();
        $this->setFlash('success', 'Bạn đã đăng xuất khỏi hệ thống.');
        $this->redirectToAction('auth', 'login');
    }
}
