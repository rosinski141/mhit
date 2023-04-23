<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\MatchHistoryController;
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

Route::get('/match/{match_id}', [MatchHistoryController::class, 'show']);