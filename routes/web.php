<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

// Fallback legacy router supporting /?controller=X&action=Y
Route::any('/', function (Request $request) {
    if ($request->has('controller')) {
        $controllerName = strtolower($request->input('controller'));
        $action = $request->input('action', 'index');

        // Handle snake case to camel case translation for methods
        if (str_contains($action, '_')) {
            $parts = explode('_', $action);
            $action = $parts[0] . implode('', array_map('ucfirst', array_slice($parts, 1)));
        }

        $controllerClass = match ($controllerName) {
            'home' => HomeController::class,
            'auth' => AuthController::class,
            'room' => RoomController::class,
            'booking' => BookingController::class,
            'payment' => PaymentController::class,
            default => null,
        };

        if ($controllerClass) {
            return app()->call([app($controllerClass), $action]);
        }
    }

    return app(HomeController::class)->index();
});

// Standard clean routes
Route::get('/about', [HomeController::class, 'about']);
Route::any('/contact', [HomeController::class, 'contact']);

Route::any('/auth/login', [AuthController::class, 'login']);
Route::any('/auth/register', [AuthController::class, 'register']);
Route::get('/auth/logout', [AuthController::class, 'logout']);
Route::any('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/auth/resend-reset-otp', [AuthController::class, 'resendResetOtp']);
Route::any('/auth/verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
Route::any('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::any('/auth/verify-account', [AuthController::class, 'verifyAccount']);
Route::get('/auth/resend-verify-otp', [AuthController::class, 'resendVerifyOtp']);

Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/detail', [RoomController::class, 'detail']);
Route::get('/rooms/search', [RoomController::class, 'search']);
Route::get('/rooms/amenities', [RoomController::class, 'amenities']);

Route::any('/booking/create', [BookingController::class, 'create']);
Route::get('/booking/success', [BookingController::class, 'success']);
Route::get('/booking/my-bookings', [BookingController::class, 'myBookings']);
Route::post('/booking/cancel', [BookingController::class, 'cancel']);
Route::post('/booking/expire', [BookingController::class, 'expire']);

Route::get('/payment/form', [PaymentController::class, 'form']);
Route::post('/payment/process', [PaymentController::class, 'process']);
Route::get('/payment/success', [PaymentController::class, 'success']);
Route::get('/payment/confirm-vietqr', [PaymentController::class, 'confirmVietQR']);
Route::get('/payment/momo-return', [PaymentController::class, 'momoReturn']);
Route::any('/api/payment/momo-ipn', [PaymentController::class, 'momoIpn']);
