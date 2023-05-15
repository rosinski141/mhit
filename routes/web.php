<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\MatchHistoryController;
use App\Http\Controllers\AccountController;
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
    return view('home');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/search', [NavigationController::class, 'search'])->name('search');

Route::get('/update', [NavigationController::class, 'update'])->name('update');

Route::get('/link_account', [NavigationController::class, 'link_account'])->name('link_account');

Route::get('/get_details', [AccountController::class, 'get_details'])->name('get_details')->middleware('auth');

Route::get('/update_feedback', [AccountController::class, 'update_feedback'])->name('update_feedback')->middleware('auth');