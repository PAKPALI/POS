<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Component\ProductController;
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
Route::get('user_login', function () {
    if(Auth::user()){
        return redirect()->route('dashboard');
    }else{
        return view('admin/login');
    }
})->name('user_login');

Route::post('admin_register', [UserController::class, "register"])->name('admin_register');

/*manage user after auth-login*/
Route::prefix('')->middleware(['auth'])->controller(UserController::class)->group(function () {
    //dashboard
    Route::get('dashboard', 'dashboard')->name('dashboard');
    //profil
    Route::get('profil', function () {return view('user/profile');})->name('profil');
    // user
    Route::resource('user', UserController::class);
    // update email
    Route::post('updateEmail', 'updateEmail');
    // update password
    Route::post('updatePassword', 'updatePassword');
});


/*manage component*/
Route::prefix('component')->middleware(['auth'])->group(function () {
    //category
    Route::controller(CategoryController::class)->group(function () {
        Route::resource('category', CategoryController::class);
    });
    //product
    Route::controller(ProductController::class)->group(function () {
        Route::resource('product', ProductController::class);
    });
});

/*manage POS*/
Route::prefix('pos')->middleware(['auth'])->group(function () {
    //sale
    Route::controller(SaleController::class)->group(function () {
        Route::resource('sale', SaleController::class);
        //history
        Route::get('history', 'history')->name('history');
    });
});

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('outUser', [UserController::class, 'outUser'])->name('outUser');