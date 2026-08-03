<?php
// routes/web.php - placeholder routes

use Illuminate\Support\Facades\Route;

Route::get('/', function() {
    return view('welcome');
});

// API placeholders
Route::prefix('api')->group(function () {
    Route::get('/health', function(){ return response()->json(['status'=>'ok']); });
});
