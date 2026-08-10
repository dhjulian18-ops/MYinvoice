<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\AppSettingController;

use App\Http\Controllers\Api\AdminController;
use App\Http\Middleware\AdminMiddleware;

use App\Http\Controllers\Api\NotificationController;

use App\Http\Controllers\Api\UserChatController;

// ── HEALTH CHECK (untuk Render.com monitoring) ───────────────
Route::get('/health', fn() => response()->json(['status' => 'ok', 'app' => 'MyInvoice API']));

// ── PUBLIC (tanpa auth) ───────────────────────────────────────
Route::post('/send-otp',        [AuthController::class, 'sendOtp']);
Route::post('/verify-otp',      [AuthController::class, 'verifyOtp']);
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/login',           [AuthController::class, 'login'])->name('login');
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

// ── PROTECTED (butuh token) ───────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
    Route::put('/me',      [AuthController::class, 'updateProfile']);
    Route::put('/me/password', [AuthController::class, 'changePassword']);

    // User Chat
    Route::get('/user/chats/unread', [UserChatController::class, 'getUnreadCount']);
    Route::get('/user/chats',        [UserChatController::class, 'getMessages']);
    Route::post('/user/chats',       [UserChatController::class, 'sendMessage']);

    // Notifications (User)
    Route::get('/notifications',           [NotificationController::class, 'indexUser']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/user/fcm-token',         [NotificationController::class, 'updateFcmToken']);

    // App Settings
    Route::get('/settings', [AppSettingController::class, 'index']);

    // Businesses
    Route::get('/businesses',              [BusinessController::class, 'index']);
    Route::post('/businesses',             [BusinessController::class, 'store']);
    Route::get('/businesses/{id}',         [BusinessController::class, 'show']);
    Route::put('/businesses/{id}',         [BusinessController::class, 'update']);
    Route::delete('/businesses/{id}',      [BusinessController::class, 'destroy']);
    Route::post('/businesses/{id}/activate', [BusinessController::class, 'activate']);

    // Clients
    Route::get('/clients',           [ClientController::class, 'index']);
    Route::post('/clients',          [ClientController::class, 'store']);
    Route::get('/clients/{id}',      [ClientController::class, 'show']);
    Route::put('/clients/{id}',      [ClientController::class, 'update']);
    Route::delete('/clients',        [ClientController::class, 'destroyAll']);
    Route::delete('/clients/{id}',   [ClientController::class, 'destroy']);

    // Items
    Route::get('/items',           [ItemController::class, 'index']);
    Route::post('/items',          [ItemController::class, 'store']);
    Route::get('/items/{id}',      [ItemController::class, 'show']);
    Route::put('/items/{id}',      [ItemController::class, 'update']);
    Route::delete('/items',        [ItemController::class, 'destroyAll']);
    Route::delete('/items/{id}',   [ItemController::class, 'destroy']);

    // Invoices
    Route::get('/invoices',           [InvoiceController::class, 'index']);
    Route::post('/invoices',          [InvoiceController::class, 'store']);
    Route::get('/invoices/{id}',      [InvoiceController::class, 'show']);
    Route::put('/invoices/{id}',      [InvoiceController::class, 'update']);
    Route::delete('/invoices',        [InvoiceController::class, 'destroyAll']);
    Route::delete('/invoices/{id}',   [InvoiceController::class, 'destroy']);

    // ── ADMIN PANEL ──────────────────────────────────────────────
    Route::middleware(AdminMiddleware::class)->prefix('admin')->group(function () {
        Route::get('/dashboard',       [AdminController::class, 'dashboard']);

        // Notifications (Admin)
        Route::get('/notifications',        [NotificationController::class, 'indexAdmin']);
        Route::post('/notifications',       [NotificationController::class, 'send']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

        // Users
        Route::get('/users',               [AdminController::class, 'getUsers']);
        Route::put('/users/{id}',          [AdminController::class, 'updateUser']);
        Route::put('/users/{id}/admin',    [AdminController::class, 'toggleAdmin']);
        Route::put('/users/{id}/block',    [AdminController::class, 'toggleBlockUser']);
        Route::delete('/users/{id}',       [AdminController::class, 'deleteUser']);

        // Admin Live Chat
        Route::get('/chats/users',         [AdminController::class, 'getChatUsers']);
        Route::get('/chats/{userId}',      [AdminController::class, 'getChatMessages']);
        Route::post('/chats',              [AdminController::class, 'sendChatMessage']);

        // Invoices
        Route::get('/invoices',            [AdminController::class, 'getInvoices']);
        Route::delete('/invoices/{id}',    [AdminController::class, 'deleteInvoice']);

        // Clients
        Route::get('/clients',             [AdminController::class, 'getClients']);
        Route::delete('/clients/{id}',     [AdminController::class, 'deleteClient']);

        // Items
        Route::get('/items',               [AdminController::class, 'getItems']);
        Route::delete('/items/{id}',       [AdminController::class, 'deleteItem']);

        // Businesses
        Route::get('/businesses',          [AdminController::class, 'getBusinesses']);

        // App Settings (Admin)
        Route::get('/settings',            [AppSettingController::class, 'adminIndex']);
        Route::post('/settings',           [AppSettingController::class, 'update']);
    });
});

