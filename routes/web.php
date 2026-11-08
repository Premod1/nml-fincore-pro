<?php

use Illuminate\Support\Facades\Route;
use Nml\FinCorePro\Http\Controllers\AccountController;
use Nml\FinCorePro\Http\Controllers\DashboardController;
use Nml\FinCorePro\Http\Controllers\JournalController;
use Nml\FinCorePro\Http\Controllers\MappingController;
use Nml\FinCorePro\Http\Controllers\ReportController;

Route::group([
    'prefix' => config('fincore-pro.route_prefix', 'accounting'),
    'middleware' => config('fincore-pro.middleware', ['web']),
    'as' => 'fincore.',
], function () {
    // 1. Overview Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Chart of Accounts
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::post('/accounts/seed-defaults', [AccountController::class, 'seedDefaults'])->name('accounts.seed');

    // 3. Journal Entries
    Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
    Route::get('/journals/create', [JournalController::class, 'create'])->name('journals.create');
    Route::post('/journals', [JournalController::class, 'store'])->name('journals.store');
    Route::get('/journals/{id}', [JournalController::class, 'show'])->name('journals.show');
    Route::post('/journals/{id}/post', [JournalController::class, 'post'])->name('journals.post');
    Route::post('/journals/{id}/void', [JournalController::class, 'void'])->name('journals.void');

    // 4. Financial Reports (Trial Balance, P&L, Balance Sheet, General Ledger)
    Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
    Route::get('/reports/income-statement', [ReportController::class, 'incomeStatement'])->name('reports.income-statement');
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('/reports/general-ledger', [ReportController::class, 'generalLedger'])->name('reports.general-ledger');

    // 5. Zero-Accounting-Knowledge ERP Posting Rules & Account Mapping UI
    Route::get('/mappings', [MappingController::class, 'index'])->name('mappings.index');
    Route::post('/mappings/sync', [MappingController::class, 'syncDefaults'])->name('mappings.sync');
    Route::put('/mappings/{ruleId}', [MappingController::class, 'update'])->name('mappings.update');
    Route::post('/mappings/test-event', [MappingController::class, 'testEvent'])->name('mappings.test');
});
