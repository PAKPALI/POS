<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Component\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/a', function () {
    return view('dashboard');
});

// manage stock
Route::prefix('component')->group(function () {
    //category
    Route::controller(CategoryController::class)->group(function () {
        Route::resource('category', CategoryController::class);
    });
    //article
    // Route::controller(ArticleController::class)->group(function () {
    //     Route::resource('article', ArticleController::class);
    // });
    // //entry
    // Route::controller(EntryController::class)->group(function () {
    //     Route::resource('entry', EntryController::class);
    // });
    // // operation
    // Route::controller(OperationController::class)->group(function () {
    //     Route::resource('operation', OperationController::class);
    // });
});
