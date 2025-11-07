<?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\CompilerController;
// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/', [CompilerController::class, 'index'])->name('index');
// Route::post('/compile', [CompilerController::class, 'compile'])->name('compile');
// Route::get('/result', [CompilerController::class, 'result'])->name('result');
// Route::get('/graphs', [CompilerController::class, 'showGraphs'])->name('show.graph');
// Route::get('/python-code', [CompilerController::class, 'showPythonCode'])->name('show.python.code');


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompilerController;

Route::get('/', [CompilerController::class, 'index'])->name('index');
Route::post('/compile', [CompilerController::class, 'compile'])->name('compile');

Route::get('/result', [CompilerController::class, 'result'])->name('result');
Route::post('/result', [CompilerController::class, 'result'])->name('result');
Route::get('/result/{timestamp?}', [CompilerController::class, 'result'])->name('result');


//Route::get('/python-code', [CompilerController::class, 'showPythonCode'])->name('show.python.code');
Route::get('/python-code/{timestamp?}', [CompilerController::class, 'showPythonCode'])->name('show.python.code');

Route::get('/graphs/{filename}', [CompilerController::class, 'showGraphs'])->name('show.graph');

Route::get('/download-csv/{filename}', [CompilerController::class, 'downloadCSV'])->name('download.csv');




