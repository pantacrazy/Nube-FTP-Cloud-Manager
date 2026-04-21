<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NubeController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Setup (solo si no hay usuarios, protegido por el propio controlador)
Route::get('/setup', [SetupController::class, 'show'])->name('setup');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Change own password
Route::middleware('auth')->group(function () {
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});

// Admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/user/{id}/reset-password', [UserController::class, 'showResetPassword'])->name('user.reset-password');
    Route::put('/user/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::get('/user/{id}/edit-role', [UserController::class, 'editRole'])->name('user.edit-role');
    Route::put('/user/{id}/edit-role', [UserController::class, 'updateRole']);
});

// User CRUD routes
Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/users', [UserController::class, 'store'])->name('user.store');
    Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');
    Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
});

// Nubes (FTP sources) CRUD routes
Route::middleware('auth')->group(function () {
    Route::get('/nubes', [NubeController::class, 'index'])->name('nubes.index');
    Route::get('/nubes/create', [NubeController::class, 'create'])->name('nubes.create');
    Route::post('/nubes', [NubeController::class, 'store'])->name('nubes.store');
    Route::get('/nubes/{nube}', [NubeController::class, 'show'])->name('nubes.show');
    Route::get('/nubes/{nube}/edit', [NubeController::class, 'edit'])->name('nubes.edit');
    Route::put('/nubes/{nube}', [NubeController::class, 'update'])->name('nubes.update');
    Route::delete('/nubes/{nube}', [NubeController::class, 'destroy'])->name('nubes.destroy');
    Route::post('/nubes/test-preview', [NubeController::class, 'testConnectionPreview'])->name('nubes.test-preview')->middleware('throttle:5,1');
    Route::post('/nubes/{nube}/test', [NubeController::class, 'testConnection'])->name('nubes.test')->middleware('throttle:5,1');
    Route::get('/nubes/{nube}/status', [NubeController::class, 'checkStatus'])->name('nubes.status')->middleware('throttle:10,1');

    // FTP File Browser
    Route::get('/nubes/{nube}/browse', [NubeController::class, 'browse'])->name('nubes.browse');
    Route::get('/nubes/{nube}/browse-items', [NubeController::class, 'browseItems'])->name('nubes.browse-items')->middleware('throttle:12,1');
    Route::post('/nubes/{nube}/folder', [NubeController::class, 'createFolder'])->name('nubes.folder.create')->middleware('throttle:10,1');
    Route::post('/nubes/{nube}/delete', [NubeController::class, 'deleteItem'])->name('nubes.delete')->middleware('throttle:10,1');
    Route::post('/nubes/{nube}/rename', [NubeController::class, 'renameItem'])->name('nubes.rename')->middleware('throttle:10,1');
    Route::post('/nubes/{nube}/upload', [NubeController::class, 'uploadFile'])->name('nubes.upload')->middleware('throttle:10,1');
    Route::post('/nubes/{nube}/download-sync', [NubeController::class, 'downloadSync'])->name('nubes.download-sync');
    Route::post('/nubes/{nube}/download-cancel', [NubeController::class, 'cancelDownload'])->name('nubes.download-cancel');
    Route::get('/nubes/{nube}/folder-size', [NubeController::class, 'getFolderSize'])->name('nubes.folder-size')->middleware('throttle:20,1');
});

// Redirect root to setup if no users, otherwise to nubes.index
Route::get('/', function () {
    if (User::count() === 0) {
        return redirect()->route('setup');
    }

    return redirect()->route('nubes.index');
})->name('home');

// Redirect root to login if not authenticated
Route::middleware('guest')->get('/login-redirect', function () {
    return redirect()->route('login');
});
