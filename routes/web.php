<?php

use Illuminate\Support\Facades\Route;

Route::any('/', function () {
    $controller = request('controller', 'home');
    $action = request('action', 'index');

    $className = 'App\\Http\\Controllers\\' . ucfirst(strtolower($controller)) . 'controller';
    if (!class_exists($className)) {
        abort(404, 'Controller not found');
    }

    $instance = new $className();
    if (!method_exists($instance, $action)) {
        abort(404, 'Action not found');
    }

    ob_start();
    $instance->$action();
    $output = ob_get_clean();
    return $output;
});

Route::any('/{controller}/{action?}', function ($controller, $action = 'index') {
    $className = 'App\\Http\\Controllers\\' . ucfirst(strtolower($controller)) . 'controller';
    if (!class_exists($className)) {
        abort(404, 'Controller not found');
    }

    $instance = new $className();
    if (!method_exists($instance, $action)) {
        abort(404, 'Action not found');
    }

    ob_start();
    $instance->$action();
    $output = ob_get_clean();
    return $output;
});
