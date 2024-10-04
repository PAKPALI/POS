<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
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

/*manage user*/

Route::prefix('')->group(function () {
    // register
    Route::get('', function () {
        $user = User::where('user_type',2)->count();
        if($user==0){
            return view('admin/register');
        }else{
            return view('admin/login');
        }
    })->name('admin.register');

    // login
    Route::get('user_login', function () {return view('admin/login');})->name('user_login');

    Route::controller(UserController::class)->group(function () {
        Route::resource('user', UserController::class);
    });
});

/*manage component*/
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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
