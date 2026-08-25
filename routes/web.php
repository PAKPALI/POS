<?php

use App\Http\Controllers\AMS\CashAccountController;
use App\Http\Controllers\AMS\DashboardController;
use App\Http\Controllers\AMS\SettingController;
use App\Http\Controllers\AMS\TransactionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\InvitationAcceptanceController;
use App\Http\Controllers\CodePromo\CodePromoController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\SwitchCompanyController;
use App\Http\Controllers\Company\NotificationSettingController;
use App\Http\Controllers\SmsQuotaController;
use App\Http\Controllers\Component\CategoryController;
use App\Http\Controllers\Component\ClientController;
use App\Http\Controllers\Component\InventoryController;
use App\Http\Controllers\Component\MenuController;
use App\Http\Controllers\Component\ProductController;
use App\Http\Controllers\Component\SupplierController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\RoleController;
use App\Http\Controllers\User\CompanyInvitationController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
        return redirect(app(\App\Services\AuthorizedLandingPage::class)->forUser(
            Auth::user(),
            session('active_company_id')
        ));
    }else{
        return response()->view('admin/login')->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
})->name('user_login');

Route::post('admin_register', [UserController::class, "register"])->name('admin_register');
Route::get('signup', fn () => response()->view('admin.register')->withHeaders([
    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    'Pragma' => 'no-cache',
    'Expires' => '0',
]))->middleware('guest')->name('signup');
Route::get('invitations/{token}', [InvitationAcceptanceController::class, 'show'])
    ->middleware('throttle:30,1')->name('invitations.show');
Route::post('invitations/{token}/accept', [InvitationAcceptanceController::class, 'accept'])
    ->middleware('throttle:10,1')->name('invitations.accept');
Route::post('invitations/{token}/decline', [InvitationAcceptanceController::class, 'decline'])
    ->middleware('throttle:10,1')->name('invitations.decline');

Route::middleware('auth')->prefix('companies')->name('companies.')->group(function () {
    Route::get('select', [SwitchCompanyController::class, 'select'])->name('select');
    Route::get('create', [CompanyController::class, 'create'])->name('create');
    Route::post('', [CompanyController::class, 'store'])->name('store');
    Route::post('{companyId}/switch', [SwitchCompanyController::class, 'switch'])
        ->middleware('throttle:20,1')
        ->name('switch');
    Route::post('leave', [SwitchCompanyController::class, 'leave'])->name('leave');
});

/*manage user after auth-login*/
Route::prefix('')->middleware(['auth', 'company.resolve', 'company.selected'])->controller(UserController::class)->group(function () {
    //dashboard
    Route::get('dashboard', 'dashboard')->middleware('permission:dashboard.view')->name('dashboard');
    //profil
    Route::get('profil', function () {return view('user/profile');})->name('profil');
    // user
    Route::post('user/attach-existing', 'attachExisting')
        ->middleware('permission:members.manage')
        ->name('user.attach-existing');
    Route::post('user/invitations', [CompanyInvitationController::class, 'store'])
        ->middleware('permission:members.manage')->name('user.invitations.store');
    Route::post('user/invitations/{invitation}/resend', [CompanyInvitationController::class, 'resend'])
        ->middleware('permission:members.manage')->name('user.invitations.resend');
    Route::delete('user/invitations/{invitation}', [CompanyInvitationController::class, 'destroy'])
        ->middleware('permission:members.manage')->name('user.invitations.destroy');
    Route::get('user/{user}/transfer-options', 'transferOptions')
        ->middleware('permission:members.manage')->name('user.transfer-options');
    Route::post('user/{user}/transfer-company', 'transferToCompany')
        ->middleware('permission:members.manage')->name('user.transfer-company');
    Route::resource('user', UserController::class)->middleware('permission:members.manage');
    Route::resource('roles', RoleController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:members.manage');
    // Route::get('getEmployeList', 'getEmployeList')->name('getEmployeList');
    // update email
    Route::post('updateEmail', 'updateEmail')->middleware('throttle:10,1')->name('profile.email.update');
    // update password
    Route::post('updatePassword', 'updatePassword')->middleware('throttle:10,1')->name('profile.password.update');

    // chart
    Route::post('/statistics/top-products', [UserController::class, 'topSellingProducts'])
        ->middleware('permission:dashboard.view')
        ->name('statistics.topProducts');

});

/*manage component*/
Route::prefix('component')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:catalog.manage'])->group(function () {
    //category
    Route::controller(CategoryController::class)->group(function () {
        Route::resource('category', CategoryController::class);
        Route::get('category-disabled', [CategoryController::class, 'disabledListing'])->name('category.disabled.listing');
    });
    //product
    Route::controller(ProductController::class)->group(function () {
        Route::resource('product', ProductController::class);
        Route::get('product-disabled', [ProductController::class, 'disabledListing'])->name('product.disabled.listing');
    });
    //supplier
    Route::controller(SupplierController::class)->group(function () {
        Route::resource('supplier', SupplierController::class);
        Route::get('supplier-disabled', [SupplierController::class, 'disabledListing'])->name('supplier.disabled.listing');
    });
    Route::get('product/export/pdf', [ProductController::class, 'exportPdf'])
    ->name('product.export.pdf');
    //menu
    Route::controller(MenuController::class)->group(function () {
        Route::resource('menu', MenuController::class);
    });
    // inventory
});

Route::prefix('component')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:inventory.manage'])->group(function () {
    Route::controller(InventoryController::class)->group(function () {
        Route::resource('inventory', InventoryController::class);
        Route::post('inventory-remove', 'remove')->name('inventory.remove');
        Route::get('inventory/export/pdf', [InventoryController::class, 'exportPdf'])->name('inventory.export.pdf');
    });
});

Route::prefix('component')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:clients.manage'])->group(function () {
    Route::resource('client', ClientController::class);
    Route::get('client-disabled', [ClientController::class, 'disabledListing'])->name('client.disabled.listing');
});

/*manage POS*/
Route::prefix('pos')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:sales.manage'])->group(function () {
    //sale
    Route::controller(SaleController::class)->group(function () {
        Route::resource('sale', SaleController::class);
        //history
        Route::get('history', 'history')->name('history');
        Route::get('history/export/pdf', 'exportHistoryPdf')->name('history.export.pdf');
        Route::get('/clients/search', 'searchClients')->name('clients.search');
        Route::get('/products/search', 'search')->name('products.search');
        Route::get('sale/invoice/{id}/pdf', 'generatePDF')->name('codePromo.pdf');
    });
});

Route::prefix('code')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:catalog.manage'])->group(function () {
    //sale
    Route::controller(CodePromoController::class)->group(function () {
        Route::resource('code', CodePromoController::class);
        Route::post('/verify-promo', [CodePromoController::class, 'verifyPromo'])->name('verifyPromo');
        Route::get('/code-promo/{id}/pdf', [CodePromoController::class, 'generatePDF'])->name('codePromo.pdf');
    });
});

Route::prefix('ams')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:cash.manage'])->group(function () {
    //dashboard
    Route::get('/dashboard-ams', [DashboardController::class, 'index'])->name('ams.dashboard');
    Route::post('/dashboard-ams/stats', [DashboardController::class, 'transactionStats'])->name('ams.stats');
    // cash account
    Route::resource('cash-account', CashAccountController::class);
    Route::resource('transaction', TransactionController::class);
    // setting
    Route::get('settings', [SettingController::class, 'index'])->name('ams.settings');
    Route::post('settings', [SettingController::class, 'store'])->name('ams.settings.store');
});

/*manage ecommerce admin*/
Route::prefix('ecommerce')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:ecommerce.manage'])->group(function () {
    Route::get('settings', [App\Http\Controllers\Ecommerce\SettingController::class, 'index'])->name('ecommerce.settings');
    Route::get('slug/check', [App\Http\Controllers\Ecommerce\SettingController::class, 'checkSlug'])->name('ecommerce.slug.check');
    Route::post('settings/update', [App\Http\Controllers\Ecommerce\SettingController::class, 'updateSettings'])->name('ecommerce.settings.update');
    Route::post('managers/add', [App\Http\Controllers\Ecommerce\SettingController::class, 'addManager'])->name('ecommerce.managers.add');
    Route::delete('managers/{id}', [App\Http\Controllers\Ecommerce\SettingController::class, 'removeManager'])->name('ecommerce.managers.remove');
    Route::get('managers/list', [App\Http\Controllers\Ecommerce\SettingController::class, 'managersList'])->name('ecommerce.managers.list');
    Route::get('orders', [App\Http\Controllers\Ecommerce\OrderController::class, 'index'])->name('ecommerce.orders.index');
    Route::get('orders/{id}', [App\Http\Controllers\Ecommerce\OrderController::class, 'show'])->name('ecommerce.orders.show');
    Route::post('orders/{id}/execute', [App\Http\Controllers\Ecommerce\OrderController::class, 'execute'])
        ->middleware('permission:sales.manage')->name('ecommerce.orders.execute');
    Route::post('orders/{id}/cancel', [App\Http\Controllers\Ecommerce\OrderController::class, 'cancel'])->name('ecommerce.orders.cancel');
});

/*public ecommerce routes*/
Route::prefix('boutique/{company:slug}')->controller(App\Http\Controllers\Ecommerce\FrontController::class)->group(function () {
    Route::get('/', 'index')->name('storefront.home');
    Route::get('/products', 'allProducts')->name('storefront.products');
    Route::get('/category/{id}', 'category')->name('storefront.category');
    Route::get('/product/{id}', 'product')->name('storefront.product');
    Route::get('/checkout', 'checkout')->name('storefront.checkout');
    Route::post('/order/place', 'placeOrder')->middleware('throttle:10,1')->name('storefront.order.place');
    Route::get('/success', 'success')->name('storefront.success');
});

Route::prefix('shop')->controller(App\Http\Controllers\Ecommerce\FrontController::class)->group(function () {
    Route::get('/', 'index')->name('shop.home');
    Route::get('/products', 'allProducts')->name('shop.products');
    Route::get('/category/{id}', 'category')->name('shop.category');
    Route::get('/product/{id}', 'product')->name('shop.product');
    Route::get('/checkout', 'checkout')->name('shop.checkout');
    Route::post('/order/place', 'placeOrder')->middleware('throttle:10,1')->name('shop.order.place');
    Route::get('/success', 'success')->name('shop.success');
});

Route::prefix('setting')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:company.manage'])->group(function () {
    //company
    Route::controller(CompanyController::class)->group(function () {
        Route::resource('company', CompanyController::class);
    });

    Route::get('sms-quota', [SmsQuotaController::class, 'index'])->name('sms-quota.index');
    Route::post('sms-quota', [SmsQuotaController::class, 'update'])->name('sms-quota.update');
});

Route::prefix('setting')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:notifications.manage'])->group(function () {
    Route::get('notifications', [NotificationSettingController::class, 'index'])->name('notifications.index');
    Route::put('notifications', [NotificationSettingController::class, 'update'])->name('notifications.update');
});

Auth::routes();
Route::get('/home', fn () => redirect(app(\App\Services\AuthorizedLandingPage::class)->forUser(
    Auth::user(),
    session('active_company_id')
)))->middleware('auth')->name('home');
Route::post('outUser', [UserController::class, 'outUser'])->name('outUser');
Route::post('/login', [LoginController::class, 'login'])->name('login');
