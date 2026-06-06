<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function render(string $view, array $data = [], string $layout = 'main')
    {
        if (!isset($data['flash'])) {
            $data['flash'] = $this->getFlash();
        }
        $content = view(str_replace('/', '.', $view), $data)->render();
        echo view('layouts.' . $layout, array_merge($data, ['content' => $content]))->render();
    }

    protected function redirect(string $url)
    {
        session()->save();
        header('Location: ' . $url);
        exit;
    }

    protected function redirectToAction(string $controller, string $action = 'index', array $params = [])
    {
        $query = http_build_query($params);
        $url = url('/' . $controller . '/' . $action . ($query ? '?' . $query : ''));
        $this->redirect($url);
    }

    protected function setFlash(string $type, string $message)
    {
        // Xóa flash cũ để tránh hiển thị thông báo lỗi cũ sau khi thành công
        session()->forget('success');
        session()->forget('error');
        session()->flash($type, $message);
    }

    protected function getFlash()
    {
        if (session()->has('success')) return ['type' => 'success', 'message' => session('success')];
        if (session()->has('error')) return ['type' => 'error', 'message' => session('error')];
        return null;
    }

    protected function requireLogin()
    {
        if (!session()->has('user') && empty($_SESSION['user'])) {
            session(['redirect_after_login' => request()->fullUrl()]);
            $_SESSION['redirect_after_login'] = request()->fullUrl();
            $this->redirectToAction('auth', 'login');
        }
    }

    protected function currentUser()
    {
        return session('user') ?? ($_SESSION['user'] ?? null);
    }

    protected function requireRole($roles)
    {
        $this->requireLogin();
        
        $user = $this->currentUser();
        // Since $_SESSION is heavily used in Authcontroller, ensure we check it if session('user') is empty
        if (!$user && isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
        }

        $userRole = $user['role'] ?? 'customer';
        
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($userRole, $roles)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập trang này.');
            $this->redirectToAction('home', 'index');
        }
    }
}



