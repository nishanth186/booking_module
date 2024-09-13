<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

// Route to display the welcome view
Route::get('/', function () {
    return view('welcome');
});

// Route to handle booking requests
Route::post('/booking/add', [BookingController::class, 'book'])->name('booking.add');

// Route to list all bookings
Route::get('/booking/list', [BookingController::class, 'list'])->name('booking.list');

// Route to clear all session data
Route::post('/booking/clear', [BookingController::class, 'clearAllSession'])->name('booking.clear');