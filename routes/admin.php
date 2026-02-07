<?php

use App\Http\Controllers\BanktransferController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomDomainRequestController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
|
| Routes for super admin users. These routes are loaded in bootstrap/app.php
| and have the 'web', 'auth', 'verified' middleware applied by default.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Super Admin Settings
    Route::post('super-admin/settings-save', [SuperAdminSettingsController::class, 'store'])->name('super.admin.settings.save');
    Route::post('super-admin/system-settings-save', [SuperAdminSettingsController::class, 'SystemStore'])->name('super.admin.system.setting.store');
    
    // Cookie Settings
    Route::post('cookie-settings-save', [SuperAdminSettingsController::class, 'CookieSetting'])->name('cookie.setting.store');
    
    // Pusher Settings
    Route::post('pusher-setting', [SuperAdminSettingsController::class, 'savePusherSettings'])->name('pusher.setting');
    
    // SEO Settings
    Route::post('seo/setting/save', [SuperAdminSettingsController::class, 'seoSetting'])->name('seo.setting.save');
    
    // Storage Settings
    Route::post('storage-settings-save', [SuperAdminSettingsController::class, 'storageStore'])->name('storage.setting.store');
    
    // AI Key Settings
    Route::post('ai/key/setting/save', [SuperAdminSettingsController::class, 'aiKeySettingSave'])->name('ai.key.setting.save');
    
    // Currency Settings
    Route::post('currency-settings', [SuperAdminSettingsController::class, 'saveCurrencySettings'])->name('super.admin.currency.settings');
    
    // Note Value
    Route::post('/update-note-value', [SuperAdminSettingsController::class, 'updateNoteValue'])->name('admin.update.note.value');
    
    // Plans Management
    Route::resource('plans', PlanController::class);
    Route::get('plan/list', [PlanController::class, 'PlanList'])->name('plan.list');
    Route::post('plan/store', [PlanController::class, 'PlanStore'])->name('plan.store');
    Route::post('update-plan-status', [PlanController::class, 'updateStatus'])->name('update.plan.status');
    Route::get('plan/order', [PlanController::class, 'orders'])->name('plan.order.index');
    Route::get('plan/refund/{id}/{user_id}', [PlanController::class, 'refund'])->name('order.refund');
    
    // Coupons Management
    Route::resource('coupons', CouponController::class);
    
    // Module Management
    Route::get('modules/list', [ModuleController::class, 'index'])->name('module.index');
    Route::get('modules/add', [ModuleController::class, 'add'])->name('module.add');
    Route::post('install-modules', [ModuleController::class, 'install'])->name('module.install');
    Route::post('remove-modules/{module}', [ModuleController::class, 'remove'])->name('module.remove');
    Route::post('modules-enable', [ModuleController::class, 'enable'])->name('module.enable');
    Route::get('cancel/add-on/{name}', [ModuleController::class, 'CancelAddOn'])->name('cancel.add.on');
    
    // Custom Domain Requests
    Route::resource('custom_domain_request', CustomDomainRequestController::class);
    Route::get('custom_domain_request/{id}/{response}', [CustomDomainRequestController::class, 'acceptRequest'])->name('custom_domain_request.request');
    
    // Bank Transfer Requests
    Route::resource('bank-transfer-request', BanktransferController::class);
    Route::post('bank-transfer-setting', [BanktransferController::class, 'setting'])->name('bank.transfer.setting');
    
    // Company/User Management (Super Admin specific)
    Route::get('users/{id}/login-with-company', [UserController::class, 'LoginWithCompany'])->name('login.with.company');
    Route::get('company-info/{id}', [UserController::class, 'CompnayInfo'])->name('company.info');
});
