<?php

use Illuminate\Support\Facades\Route;

Route::post('/image-to-text/retranslate', [App\Http\Controllers\ImageToTextController::class, 'retranslate'])->name('image-to-text.retranslate');
Route::post('/image-to-text/explain', [App\Http\Controllers\ImageToTextController::class, 'explain'])->name('image-to-text.explain');
Route::post('/image-to-text/store', [App\Http\Controllers\ImageToTextController::class, 'store'])->name('image-to-text.store');

Route::get('/', function () {
    return view('index');
});