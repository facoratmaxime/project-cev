<?php

use App\Http\Controllers\ProcessController;
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

Route::get('/', function () {
    return view('index');
})->name('index');


Route::post('upload_file', [ProcessController::class, 'upload'])->name('process.upload');
Route::get('no_upload_file', [ProcessController::class, 'upload'])->name('process.upload_without_upload');
