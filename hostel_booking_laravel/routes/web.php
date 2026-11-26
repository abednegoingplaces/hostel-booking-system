<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HostelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\Warden;
use Illuminate\Support\Facades\Route;

// ---- Public pages -------------------------------------------------------------
Route::get('/', HomeController::class)->name('home');
Route::resource('hostels', HostelController::class)->only(['index', 'show']);
Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

// ---- Logged-in users ------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ---- Students --------------------------------------------------------------------
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

// ---- Warden ----------------------------------------------------------------------
Route::middleware(['auth', 'role:warden'])->prefix('warden')->name('warden.')->group(function () {
    Route::resource('hostels', Warden\HostelController::class)->except('show');
    Route::resource('rooms', Warden\RoomController::class)->except('show');
    Route::get('/bookings', [Warden\BookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}/approve', [Warden\BookingController::class, 'approve'])->name('bookings.approve');
    Route::patch('/bookings/{booking}/reject', [Warden\BookingController::class, 'reject'])->name('bookings.reject');
});

require __DIR__.'/auth.php';
