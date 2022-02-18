<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RobotController;
use App\Http\Controllers\OrderController;

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

Route::get('/', function () {
    return view('index');
});

Route::post('/create/robot', [RobotController::class, 'create_robot']);
Route::post('/remove/robot', [RobotController::class, 'remove_robot']);

Route::post('/create/normal_order', [OrderController::class, 'create_normal_order']);
Route::post('/create/vip_order', [OrderController::class, 'create_vip_order']);

Route::post('/read/pending', [OrderController::class, 'read_pending']);
Route::post('/read/complete', [OrderController::class, 'read_complete']);
Route::post('/read/robot', [OrderController::class, 'read_robot']);
