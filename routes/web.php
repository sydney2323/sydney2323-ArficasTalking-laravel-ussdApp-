<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdController;


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

Route::get('/ussd',[UssdController::class,'ussdRequestHandler']);

Route::get('/', function () {
    return view('welcome');
});

