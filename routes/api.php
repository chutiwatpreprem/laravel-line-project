<?php

use App\Http\Controllers\LoginController;
use App\Jobs\QueueJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('test', [LoginController::class, 'test'])->name('test');

Route::get('notiapi', [LoginController::class, 'notiapi'])->name('notiapi');

Route::get('pushmsg', [LoginController::class, 'pushmsg'])->name('pushmsg');

Route::get('job', function () {
    //QueueJobs::dispatch();
    QueueJobs::dispatch()->delay(now()->addMinutes(1));
    return "Send OK";
});
