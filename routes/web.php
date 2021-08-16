<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::middleware(['checkApiKey'])->group(function () {
    Route::get('/subscribers', [SubscriberController::class, 'index'])->name('subscribers.list');
    Route::get('/subscribers/{id}', [SubscriberController::class, 'show'])->name('subscriber.show');
    Route::post('/subscribers', [SubscriberController::class, 'store'])->name('subscriber.store');
    Route::put('/subscribers/{id}', [SubscriberController::class, 'update'])->name('subscriber.update');
    Route::delete('/subscribers/{id}', [SubscriberController::class, 'destroy'])->name('subscriber.delete');
});

Route::get('/', [ApiController::class, 'index'])->name('home');
Route::post('/', [ApiController::class, 'store'])->name('api.store');
Route::put('/', [ApiController::class, 'update'])->name('api.update');
