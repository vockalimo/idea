<?php

use App\Http\Controllers\FileController;
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
    return view('welcome');
});

Route::get('/helloworld', function () {
    return view('helloworld');
});

Route::get('/imagesearch', function () {
    return view('imagesearch');
});

Route::post('/uploadImage', [FileController::class, 'upload'])->name('upload');

Route::get('/test', [\App\Http\Controllers\VisionController::class, 'test'])->name('test');
Route::get('/testv1', [\App\Http\Controllers\VisionController::class, 'ProductSearch'])->name("testv2");



