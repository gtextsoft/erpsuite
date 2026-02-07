<?php

use App\Http\Controllers\Company\SettingsController as CompanySettingsController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\HelpdeskConversionController;
use App\Http\Controllers\HelpdeskTicketController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseDebitNoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\WorkSpaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Company Routes
|--------------------------------------------------------------------------
|
| Routes for company users. These routes are loaded in bootstrap/app.php
| and have the 'web', 'auth', 'verified' middleware applied by default.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Roles & Permissions
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    
    // Company Settings
    Route::post('settings-save', [CompanySettingsController::class, 'store'])->name('settings.save');
    Route::post('company/settings-save', [CompanySettingsController::class, 'store'])->name('company.settings.save');
    Route::post('company/system-settings-save', [CompanySettingsController::class, 'SystemStore'])->name('company.system.setting.store');
    Route::post('company-setting-save', [CompanySettingsController::class, 'companySettingStore'])->name('company.setting.save');
    Route::post('comapny-currency-settings', [CompanySettingsController::class, 'saveCompanyCurrencySettings'])->name('company.setting.currency.settings');
    
    // Workspace Management
    Route::resource('workspace', WorkSpaceController::class);
    Route::get('workspace/change/{id}', [WorkSpaceController::class, 'change'])->name('workspace.change');
    Route::post('workspace/check', [WorkSpaceController::class, 'workspaceCheck'])->name('workspace.check');
    
    // User Management
    Route::resource('users', UserController::class);
    Route::get('users/list/view', [UserController::class, 'List'])->name('users.list.view');
    Route::any('user-reset-password/{id}', [UserController::class, 'UserPassword'])->name('users.reset');
    Route::get('user-login/{id}', [UserController::class, 'LoginManage'])->name('users.login');
    Route::post('user-reset-password/{id}', [UserController::class, 'UserPasswordReset'])->name('user.password.update');
    Route::post('user-unable', [UserController::class, 'UserUnable'])->name('user.unable');
    Route::get('users/{id}/change-workspace', [UserController::class, 'changeWorkspace'])->name('users.change.workspace');
    Route::post('users/{id}/update-workspace', [UserController::class, 'updateWorkspace'])->name('users.update.workspace');
    
    // User Logs
    Route::get('users/logs/history', [UserController::class, 'UserLogHistory'])->name('users.userlog.history');
    Route::get('users/logs/{id}', [UserController::class, 'UserLogView'])->name('users.userlog.view');
    Route::delete('users/logs/destroy/{id}', [UserController::class, 'UserLogDestroy'])->name('users.userlog.destroy');
    
    // User Import
    Route::get('users/import/export', [UserController::class, 'fileImportExport'])->name('users.file.import');
    Route::get('users/import/modal', [UserController::class, 'fileImportModal'])->name('users.import.modal');
    Route::post('users/import', [UserController::class, 'fileImport'])->name('users.import');
    Route::post('users/data/import/', [UserController::class, 'UserImportdata'])->name('users.import.data');
    
    // Email Templates
    Route::resource('email-templates', EmailTemplateController::class);
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'show'])->name('manage.email.language');
    Route::put('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name('store.email.language');
    Route::put('email_template_status/{id}', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language');
    
    // Notification Templates
    Route::resource('notification-template', NotificationController::class);
    Route::get('notification-template/{id}/{lang?}', [NotificationController::class, 'show'])->name('manage.notification.language');
    Route::post('notification-template/{pid}', [NotificationController::class, 'storeNotificationLang'])->name('store.notification.language');
    
    // Helpdesk Management
    Route::resource('helpdesk', HelpdeskTicketController::class);
    Route::get('helpdesk-tickets/search/{status?}', [HelpdeskTicketController::class, 'index'])->name('helpdesk-tickets.search');
    Route::post('helpdesk-ticket/getUser', [HelpdeskTicketController::class, 'getUser'])->name('helpdesk-tickets.getuser');
    Route::post('helpdesk-ticket/{id}/conversion', [HelpdeskConversionController::class, 'store'])->name('helpdesk-ticket.conversion.store');
    Route::post('helpdesk-ticket/{id}/note', [HelpdeskTicketController::class, 'storeNote'])->name('helpdesk-ticket.note.store');
    Route::delete('helpdesk-ticket-attachment/{tid}/destroy/{id}', [HelpdeskTicketController::class, 'attachmentDestroy'])->name('helpdesk-ticket.attachment.destroy');
    
    // Account Module Routes (Invoice, Proposal, Purchase)
    Route::middleware('PlanModuleCheck:Account-Taskly')->group(function () {
        // Invoice Routes
        Route::post('invoice/customer', [InvoiceController::class, 'customer'])->name('invoice.customer');
        Route::post('invoice-attechment/{id}', [InvoiceController::class, 'invoiceAttechment'])->name('invoice.file.upload');
        Route::delete('invoice-attechment/destroy/{id}', [InvoiceController::class, 'invoiceAttechmentDestroy'])->name('invoice.attachment.destroy');
        Route::post('invoice/product', [InvoiceController::class, 'product'])->name('invoice.product');
        Route::get('invoice/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoice.duplicate');
        Route::get('invoice/{id}/recurring', [InvoiceController::class, 'recurring'])->name('invoice.recurring');
        Route::get('invoice/items', [InvoiceController::class, 'items'])->name('invoice.items');
        Route::post('invoice/product/destroy', [InvoiceController::class, 'productDestroy'])->name('invoice.product.destroy');
        Route::get('invoice/grid/view', [InvoiceController::class, 'Grid'])->name('invoice.grid.view');
        Route::resource('invoice', InvoiceController::class);
        Route::get('invoice/create/{cid}', [InvoiceController::class, 'create'])->name('invoice.create');
        Route::get('invoice/{id}/sent', [InvoiceController::class, 'sent'])->name('invoice.sent');
        Route::get('invoice/{id}/resent', [InvoiceController::class, 'resent'])->name('invoice.resent');
        Route::get('invoice/{id}/payment/reminder', [InvoiceController::class, 'paymentReminder'])->name('invoice.payment.reminder');
        Route::get('invoice/{id}/payment', [InvoiceController::class, 'payment'])->name('invoice.payment');
        Route::post('invoice/{id}/payment', [InvoiceController::class, 'createPayment'])->name('invoice.payment');
        Route::post('invoice/{id}/payment/{pid}/', [InvoiceController::class, 'paymentDestroy'])->name('invoice.payment.destroy');
        Route::get('invoice/{id}/send', [InvoiceController::class, 'customerInvoiceSend'])->name('invoice.send');
        Route::post('invoice/{id}/send/mail', [InvoiceController::class, 'customerInvoiceSendMail'])->name('invoice.send.mail');
        Route::post('invoice/section/type', [InvoiceController::class, 'InvoiceSectionGet'])->name('invoice.section.type');
        Route::get('invoice/status/view', [InvoiceController::class, 'InvocieStatus'])->name('invoice.status.view');
        
        // Proposal Routes
        Route::post('proposal-attechment/{id}', [ProposalController::class, 'proposalAttechment'])->name('proposal.file.upload');
        Route::delete('proposal-attechment/destroy/{id}', [ProposalController::class, 'proposalAttechmentDestroy'])->name('proposal.attachment.destroy');
        Route::post('proposal/customer', [ProposalController::class, 'customer'])->name('proposal.customer');
        Route::post('proposal/product', [ProposalController::class, 'product'])->name('proposal.product');
        Route::get('proposal/{id}/convert', [ProposalController::class, 'convert'])->name('proposal.convert');
        Route::get('proposal/{id}/duplicate', [ProposalController::class, 'duplicate'])->name('proposal.duplicate');
        Route::get('proposal/items', [ProposalController::class, 'items'])->name('proposal.items');
        Route::post('proposal/product/destroy', [ProposalController::class, 'productDestroy'])->name('proposal.product.destroy');
        Route::resource('proposal', ProposalController::class);
        Route::get('proposal/grid/view', [ProposalController::class, 'Grid'])->name('proposal.grid.view');
        Route::get('proposal/create/{cid}', [ProposalController::class, 'create'])->name('proposal.create');
        Route::get('proposal/{id}/status/change', [ProposalController::class, 'statusChange'])->name('proposal.status.change');
        Route::get('proposal/{id}/resent', [ProposalController::class, 'resent'])->name('proposal.resent');
        Route::post('proposal/section/type', [ProposalController::class, 'ProposalSectionGet'])->name('proposal.section.type');
        Route::get('proposal/{id}/sent', [ProposalController::class, 'sent'])->name('proposal.sent');
        Route::get('proposal/stats/view', [ProposalController::class, 'ProposalQuickStats'])->name('proposal.stats.view');
        
        // Purchase Routes
        Route::resource('purchases', PurchaseController::class);
        Route::get('purchases-grid', [PurchaseController::class, 'grid'])->name('purchases.grid');
        Route::post('purchases/items', [PurchaseController::class, 'items'])->name('purchases.items');
        Route::get('purchases/{id}/payment', [PurchaseController::class, 'payment'])->name('purchases.payment');
        Route::post('purchases/{id}/payment', [PurchaseController::class, 'createPayment'])->name('purchases.payment');
        Route::post('purchases/{id}/payment/{pid}/destroy', [PurchaseController::class, 'paymentDestroy'])->name('purchases.payment.destroy');
        Route::post('purchases/product/destroy', [PurchaseController::class, 'productDestroy'])->name('purchases.product.destroy');
        Route::post('purchases/vender', [PurchaseController::class, 'vender'])->name('purchases.vender');
        Route::post('purchases/product', [PurchaseController::class, 'product'])->name('purchases.product');
        Route::get('purchases/create/{cid}', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::get('purchases/{id}/sent', [PurchaseController::class, 'sent'])->name('purchases.sent');
        Route::get('purchases/{id}/resent', [PurchaseController::class, 'resent'])->name('purchases.resent');
        
        // Purchase Debit Notes
        Route::get('purchases/{id}/debit-note', [PurchaseDebitNoteController::class, 'create'])->name('purchases.debit.note');
        Route::post('purchases/{id}/debit-note', [PurchaseDebitNoteController::class, 'store']);
        Route::get('purchases/{id}/debit-note/edit/{cn_id}', [PurchaseDebitNoteController::class, 'edit'])->name('purchases.edit.debit.note');
        Route::post('purchases/{id}/debit-note/edit/{cn_id}', [PurchaseDebitNoteController::class, 'update']);
        Route::delete('purchases/{id}/debit-note/delete/{cn_id}', [PurchaseDebitNoteController::class, 'destroy'])->name('purchases.delete.debit.note');
        
        // Purchase Attachments
        Route::post('purchase/{id}/file', [PurchaseController::class, 'fileUpload'])->name('purchases.files.upload');
        Route::delete('purchase/{id}/destroy', [PurchaseController::class, 'fileUploadDestroy'])->name('purchases.attachment.destroy');
        
        // Warehouse Management
        Route::resource('warehouses', WarehouseController::class);
        Route::get('warehouses/import/export', [WarehouseController::class, 'fileImportExport'])->name('warehouses.file.import');
        Route::post('warehouses/import', [WarehouseController::class, 'fileImport'])->name('warehouses.import');
        Route::get('warehouses/import/modal', [WarehouseController::class, 'fileImportModal'])->name('warehouses.import.modal');
        Route::post('warehouses/data/import/', [WarehouseController::class, 'warehouseImportdata'])->name('warehouses.import.data');
        Route::get('productservice/{id}/detail', [WarehouseController::class, 'warehouseDetail'])->name('productservices.detail');
        
        // Warehouse Transfer
        Route::resource('warehouses-transfer', WarehouseTransferController::class);
        Route::post('warehouses-transfer/getproduct', [WarehouseTransferController::class, 'getproduct'])->name('warehouses-transfer.getproduct');
        Route::post('warehouses-transfer/getquantity', [WarehouseTransferController::class, 'getquantity'])->name('warehouses-transfer.getquantity');
        
        // Reports
        Route::get('reports-warehouses', [ReportController::class, 'warehouseReport'])->name('reports.warehouse');
        Route::get('reports-daily-purchases', [ReportController::class, 'purchaseDailyReport'])->name('reports.daily.purchase');
        Route::get('reports-monthly-purchases', [ReportController::class, 'purchaseMonthlyReport'])->name('reports.monthly.purchase');
    });
    
    // Template Settings
    Route::post('/invoices/template/setting', [InvoiceController::class, 'saveTemplateSettings'])->name('invoice.template.setting');
    Route::get('/invoices/preview/{template}/{color}', [InvoiceController::class, 'previewInvoice'])->name('invoice.preview');
    Route::get('/proposal/preview/{template}/{color}', [ProposalController::class, 'previewInvoice'])->name('proposal.preview');
    Route::post('/proposal/template/setting', [ProposalController::class, 'saveTemplateSettings'])->name('proposal.template.setting');
    Route::get('purchases/preview/{template}/{color}', [PurchaseController::class, 'previewPurchase'])->name('purchases.preview');
    Route::post('/purchase/template/setting', [PurchaseController::class, 'savePurchaseTemplateSettings'])->name('purchases.template.setting');
});
