<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BanktransferController;
use App\Http\Controllers\HelpdeskTicketController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
|
| Routes for client/guest users and public access. These routes are loaded
| in bootstrap/app.php with the 'web' middleware group.
|
*/

// Custom domain check routes
Route::middleware('domain-check')->group(function () {
    Route::get('/register/{lang?}', [RegisteredUserController::class, 'create'])->name('register');
    Route::get('/login/{lang?}', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::get('/forgot-password/{lang?}', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::get('/verify-email/{lang?}', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
    
    // Public pages
    Route::get('add-on', [HomeController::class, 'Software'])->name('apps.software');
    Route::get('add-on/details/{slug}', [HomeController::class, 'SoftwareDetails'])->name('software.details');
    Route::get('pricing', [HomeController::class, 'Pricing'])->name('apps.pricing');
    Route::get('/', [HomeController::class, 'index'])->name('start');
});

// Public Plan Routes
Route::get('plan/active', [PlanController::class, 'ActivePlans'])->name('active.plans');
Route::any('plan/package-data', [PlanController::class, 'PackageData'])->name('package.data');
Route::get('plan/plan-buy/{id}', [PlanController::class, 'PlanBuy'])->name('plan.buy');
Route::get('plan/plan-trial/{id}', [PlanController::class, 'PlanTrial'])->name('plan.trial');
Route::get('add-one/detail/{id}', [PlanController::class, 'AddOneDetail'])->name('add-one.detail');
Route::post('add-one/detail/save/{id}', [PlanController::class, 'AddOneDetailSave'])->name('add-one.detail.save');
Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon');

// Cookie Consent
Route::get('cookie/consent', [SuperAdminSettingsController::class, 'CookieConsent'])->name('cookie.consent');

// Public Helpdesk Routes
Route::post('helpdesk-ticket/{id}', [HelpdeskTicketController::class, 'reply'])->name('helpdesk-ticket.reply');
Route::get('helpdesk-ticket-show/{id}', [HelpdeskTicketController::class, 'show'])->name('helpdesk.view');

// Public Invoice Routes
Route::get('/invoice/pay/{invoice}', [InvoiceController::class, 'payinvoice'])->name('pay.invoice');
Route::get('invoice/pdf/{id}', [InvoiceController::class, 'invoice'])->name('invoice.pdf');
Route::post('/bank/transfer/invoice', [BanktransferController::class, 'invoicePayWithBank'])->name('invoice.pay.with.bank');

// Public Proposal Routes
Route::get('/proposal/pay/{proposal}', [ProposalController::class, 'payproposal'])->name('pay.proposalpay');
Route::get('proposal/pdf/{id}', [ProposalController::class, 'proposal'])->name('proposal.pdf');

// Public Purchase Routes
Route::get('/vendor/purchases/{id}/', [PurchaseController::class, 'purchaseLink'])->name('purchases.link.copy');
Route::get('/vend0r/bill/{id}/', [PurchaseController::class, 'invoiceLink'])->name('bill.link.copy')->middleware('auth');
Route::get('purchases/pdf/{id}', [PurchaseController::class, 'purchase'])->name('purchases.pdf');

// Bank Transfer for Plans
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/bank/transfer/pay', [BanktransferController::class, 'planPayWithBank'])->name('plan.pay.with.bank');
    Route::get('invoice-bank-request/{id}', [BanktransferController::class, 'invoiceBankRequestEdit'])->name('invoice.bank.request.edit');
    Route::post('bank-transfer-request-edit/{id}', [BanktransferController::class, 'invoiceBankRequestupdate'])->name('invoice.bank.request.update');
});
