<?php

use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

// Public JSON API (prefix /api is added in bootstrap/app.php).
Route::get('/rooms', [RoomController::class, 'index'])->name('api.rooms.index');
