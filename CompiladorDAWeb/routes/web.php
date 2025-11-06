<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompilerController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [CompilerController::class, 'index'])->name('index');
Route::post('/compile', [CompilerController::class, 'compile'])->name('compile');
Route::get('/result', [CompilerController::class, 'result'])->name('result');
Route::get('/graphs', [CompilerController::class, 'showGraphs'])->name('show.graph');