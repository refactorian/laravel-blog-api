<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password/{token}', function () {
    return response()->json(['message' => 'Password reset page.']);
})->middleware('signed')->name('password.reset');
