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
        if (empty($_SESSION['user'])) {
            $_SESSION['redirect_after_login'] = request()->fullUrl();
            $this->redirectToAction('auth', 'login');
        }
    }

    protected function currentUser()
    {
        return $_SESSION['user'] ?? null;
    }
}



