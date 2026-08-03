<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SaleController;

Route::post('/images/upload', [ImageUploadController::class, 'upload']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/me', [AuthController::class, 'me']);

Route::post('/sales', [SaleController::class, 'store']);
