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

// register
Route::get('', function () {
    $user = User::where('user_type',2)->count();
    if($user==0){
        return view('admin/register');
    }else{
        return view('admin/login');
    }
})->name('user_verify_auth');

// manage user before auth-login
Route::get('user_login', function () {return view('admin/login');})->name('user_login');
Route::post('admin_register', [UserController::class, "register"])->name('admin_register');

// manage user after auth-login
Route::prefix('')->middleware(['auth'])->group(function () {
    //dashboard
    Route::get('dashboard', function () {return view('dashboard');})->name('dashboard');

    Route::controller(UserController::class)->group(function () {
        Route::resource('user', UserController::class);
    });
});

/*manage component*/
Route::prefix('component')->middleware(['auth'])->group(function () {
    //category
    Route::controller(CategoryController::class)->group(function () {
        Route::resource('category', CategoryController::class);
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
