<?php

use App\Http\Controllers\Api\PollController;
use Illuminate\Support\Facades\Route;

Route::prefix('polls')->group(function () {
    Route::post('/',                    [PollController::class, 'store']);
    Route::get('/{short_code}',         [PollController::class, 'show']);
    Route::post('/{short_code}/vote',   [PollController::class, 'vote']);
});
