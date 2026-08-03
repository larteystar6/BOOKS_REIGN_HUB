<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ImageUploadController;

Route::get('/health', function(){ return response()->json(['status'=>'ok']); });

Route::post('/images/upload', [ImageUploadController::class, 'upload']);
