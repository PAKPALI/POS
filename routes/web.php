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
use App\Http\Controllers\CommunicationLogController;
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
use App\Http\Controllers\Platform\AuthController as PlatformAuthController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\CompanyController as PlatformCompanyController;
use App\Http\Controllers\Platform\UserController as PlatformUserController;
use App\Http\Controllers\Platform\PaymentController as PlatformPaymentController;
use App\Http\Controllers\Platform\SettingController as PlatformSettingController;
use App\Http\Controllers\Platform\AuditController as PlatformAuditController;
use App\Http\Controllers\Platform\HealthController as PlatformHealthController;
use App\Http\Controllers\Platform\AdminController as PlatformAdminController;
use App\Http\Controllers\Platform\AlertController as PlatformAlertController;
use App\Http\Controllers\Platform\CommunicationController as PlatformCommunicationController;
use App\Http\Controllers\Platform\GeneralSettingController as PlatformGeneralSettingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('admin-saas', '/platform/login')->name('platform.entry');

Route::prefix('platform')->name('platform.')->group(function () {
    Route::get('login', [PlatformAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [PlatformAuthController::class, 'login'])
        ->middleware('throttle:10,1')->name('login.submit');
    Route::get('two-factor', [PlatformAuthController::class, 'showTwoFactor'])->name('two-factor.challenge');
    Route::post('two-factor', [PlatformAuthController::class, 'verifyTwoFactor'])->middleware('throttle:10,1')->name('two-factor.verify');
    Route::post('two-factor/resend', [PlatformAuthController::class, 'resendTwoFactor'])->middleware('throttle:2,1')->name('two-factor.resend');
    Route::get('forgot-password', [PlatformAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [PlatformAuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('reset-password/{token}', [PlatformAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password', [PlatformAuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.reset.update');

    Route::middleware('platform.admin')->group(function () {
        Route::get('password/change', [PlatformAuthController::class, 'editPassword'])->name('password.edit');
        Route::put('password', [PlatformAuthController::class, 'updatePassword'])->middleware('throttle:5,1')->name('password.update');
        Route::middleware('platform.password')->group(function () {
            Route::get('', [PlatformDashboardController::class, 'index'])->middleware('platform.permission:platform.dashboard.view')->name('dashboard');
            Route::get('companies', [PlatformCompanyController::class, 'index'])->middleware('platform.permission:platform.companies.view')->name('companies.index');
            Route::get('companies/{company}', [PlatformCompanyController::class, 'show'])->middleware('platform.permission:platform.companies.view')->name('companies.show');
            Route::patch('companies/{company}/status', [PlatformCompanyController::class, 'updateStatus'])
                ->middleware(['platform.permission:platform.companies.manage', 'throttle:20,1'])->name('companies.status');
            Route::get('users', [PlatformUserController::class, 'index'])->middleware('platform.permission:platform.users.view')->name('users.index');
            Route::get('users/{user}', [PlatformUserController::class, 'show'])->middleware('platform.permission:platform.users.view')->name('users.show');
            Route::get('payments', [PlatformPaymentController::class, 'index'])->middleware('platform.permission:platform.payments.view')->name('payments.index');
            Route::get('payments/{payment}', [PlatformPaymentController::class, 'show'])->middleware('platform.permission:platform.payments.view')->name('payments.show');
            Route::post('payments/{payment}/reconcile', [PlatformPaymentController::class, 'reconcile'])
                ->middleware(['platform.permission:platform.payments.reconcile', 'throttle:10,1'])->name('payments.reconcile');
            Route::get('settings', [PlatformSettingController::class, 'edit'])->middleware('platform.permission:platform.pricing.manage')->name('settings.edit');
            Route::put('settings/pricing', [PlatformSettingController::class, 'update'])
                ->middleware(['platform.permission:platform.pricing.manage', 'throttle:10,1'])->name('settings.pricing.update');
            Route::get('settings/general', [PlatformGeneralSettingController::class, 'edit'])->middleware('platform.permission:platform.admins.manage')->name('settings.general');
            Route::put('settings/general', [PlatformGeneralSettingController::class, 'update'])->middleware(['platform.permission:platform.admins.manage','throttle:10,1'])->name('settings.general.update');
            Route::get('audit', [PlatformAuditController::class, 'index'])->middleware('platform.permission:platform.audit.view')->name('audit.index');
            Route::get('audit/{audit}', [PlatformAuditController::class, 'show'])->middleware('platform.permission:platform.audit.view')->name('audit.show');
            Route::get('health', [PlatformHealthController::class, 'index'])->middleware('platform.permission:platform.health.view')->name('health.index');
            Route::post('health/jobs/{uuid}/retry', [PlatformHealthController::class, 'retryJob'])
                ->whereUuid('uuid')->middleware(['platform.permission:platform.health.jobs.retry', 'throttle:10,1'])->name('health.jobs.retry');
            Route::get('alerts', [PlatformAlertController::class, 'index'])->middleware('platform.permission:platform.health.view')->name('alerts.index');
            Route::put('alerts/settings', [PlatformAlertController::class, 'updateSettings'])->middleware(['platform.permission:platform.health.view', 'throttle:10,1'])->name('alerts.settings');
            Route::post('alerts/check', [PlatformAlertController::class, 'check'])->middleware(['platform.permission:platform.health.view', 'throttle:5,1'])->name('alerts.check');
            Route::post('alerts/{alert}/acknowledge', [PlatformAlertController::class, 'acknowledge'])->middleware('platform.permission:platform.health.view')->name('alerts.acknowledge');
            Route::post('alerts/{alert}/resolve', [PlatformAlertController::class, 'resolve'])->middleware('platform.permission:platform.health.view')->name('alerts.resolve');
            Route::get('communications', [PlatformCommunicationController::class, 'index'])->middleware('platform.permission:platform.communications.view')->name('communications.index');
            Route::get('communications/export/{format}', [PlatformCommunicationController::class, 'export'])->middleware('platform.permission:platform.communications.view')->name('communications.export');
            Route::post('communications/{delivery}/retry', [PlatformCommunicationController::class, 'retry'])->middleware(['platform.permission:platform.communications.retry', 'throttle:10,1'])->name('communications.retry');
            Route::resource('admins', PlatformAdminController::class)->only(['index', 'store', 'edit', 'update'])
                ->middleware('platform.permission:platform.admins.manage');
            Route::patch('admins/{admin}/status', [PlatformAdminController::class, 'updateStatus'])
                ->middleware(['platform.permission:platform.admins.manage', 'throttle:10,1'])->name('admins.status');
            Route::post('admins/{admin}/two-factor/reset', [PlatformAdminController::class, 'resetTwoFactor'])
                ->middleware(['platform.permission:platform.admins.manage', 'throttle:10,1'])->name('admins.two-factor.reset');
            Route::patch('admins/{admin}/two-factor', [PlatformAdminController::class, 'updateTwoFactor'])
                ->middleware(['platform.permission:platform.admins.manage', 'throttle:10,1'])->name('admins.two-factor.update');
        });
        Route::post('logout', [PlatformAuthController::class, 'logout'])->name('logout');
    });
});

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
    if (!User::exists()) {
        return view('admin/register');
    }

    return view('admin/login');
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

Route::post('admin_register', [UserController::class, "register"])
    ->middleware(['guest', 'throttle:5,1'])->name('admin_register');
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
    Route::put('profile/appearance', 'updateAppearance')->middleware('throttle:20,1')->name('profile.appearance.update');

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
    Route::get('product/export/{format}', [ProductController::class, 'exportTabular'])
        ->whereIn('format', ['csv', 'excel'])->name('product.export.tabular');
    //menu
    Route::controller(MenuController::class)->group(function () {
        Route::get('menu-products/search', 'searchProducts')->name('menu.products.search');
        Route::resource('menu', MenuController::class);
    });
    // inventory
});

Route::prefix('component')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:inventory.manage'])->group(function () {
    Route::controller(InventoryController::class)->group(function () {
        Route::resource('inventory', InventoryController::class);
        Route::post('inventory-remove', 'remove')->name('inventory.remove');
        Route::get('inventory-products/search', 'searchProducts')->name('inventory.products.search');
        Route::get('inventory-suppliers/search', 'searchSuppliers')->name('inventory.suppliers.search');
        Route::get('inventory/export/pdf', [InventoryController::class, 'exportPdf'])->name('inventory.export.pdf');
        Route::get('inventory/export/{format}', [InventoryController::class, 'exportTabular'])
            ->whereIn('format', ['csv', 'excel'])->name('inventory.export.tabular');
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
        Route::get('history/export/{format}', 'exportHistoryTabular')
            ->whereIn('format', ['csv', 'excel'])->name('history.export.tabular');
        Route::get('/clients/search', 'searchClients')->name('clients.search');
        Route::get('/products/search', 'search')->name('products.search');
        Route::get('sale/invoice/{id}/pdf', 'generatePDF')->name('codePromo.pdf');
        Route::post('sale/{sale}/send-invoice', 'sendInvoice')->middleware('throttle:10,1')->name('sale.send-invoice');
        Route::get('sale/{sale}/receipt', 'receipt')->name('sale.receipt');
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
    Route::get('users/search', [App\Http\Controllers\Ecommerce\SettingController::class, 'searchUsers'])->name('ecommerce.users.search');
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
    Route::get('/search', 'searchSuggestions')->middleware('throttle:60,1')->name('storefront.search');
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

});

Route::prefix('setting')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:quota.manage'])->group(function () {
    Route::get('sms-quota', [SmsQuotaController::class, 'index'])->name('sms-quota.index');
    Route::post('sms-quota/checkout', [SmsQuotaController::class, 'checkout'])->middleware('throttle:10,1')->name('sms-quota.checkout');
    Route::get('sms-quota/status/{transactionId}', [SmsQuotaController::class, 'status'])->middleware('throttle:60,1')->name('sms-quota.status');
    Route::get('sms-quota/return', [SmsQuotaController::class, 'returned'])->name('sms-quota.return');
});

Route::prefix('setting')->middleware(['auth', 'company.resolve', 'company.selected', 'permission:notifications.manage'])->group(function () {
    Route::get('notifications', [NotificationSettingController::class, 'index'])->name('notifications.index');
    Route::put('notifications', [NotificationSettingController::class, 'update'])->name('notifications.update');
});
Route::get('setting/communications', [CommunicationLogController::class, 'index'])
    ->middleware(['auth', 'company.resolve', 'company.selected', 'permission:communications.view'])
    ->name('communications.index');

Auth::routes();
Route::get('/home', fn () => redirect(app(\App\Services\AuthorizedLandingPage::class)->forUser(
    Auth::user(),
    session('active_company_id')
)))->middleware('auth')->name('home');
Route::post('outUser', [UserController::class, 'outUser'])->name('outUser');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware(['guest', 'throttle:10,1'])->name('login');
