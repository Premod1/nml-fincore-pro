<?php

use Illuminate\Support\Facades\Route;
use Nml\FinCorePro\Http\Controllers\AccountController;
use Nml\FinCorePro\Http\Controllers\AuditLogController;
use Nml\FinCorePro\Http\Controllers\BankReconciliationController;
use Nml\FinCorePro\Http\Controllers\BudgetController;
use Nml\FinCorePro\Http\Controllers\DashboardController;
use Nml\FinCorePro\Http\Controllers\FixedAssetController;
use Nml\FinCorePro\Http\Controllers\JournalController;
use Nml\FinCorePro\Http\Controllers\MappingController;
use Nml\FinCorePro\Http\Controllers\ReportController;
use Nml\FinCorePro\Http\Controllers\SettingsController;

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

    // 4. Banking & Reconciliations
    Route::get('/banking/reconciliations', [BankReconciliationController::class, 'index'])->name('banking.reconciliations.index');
    Route::post('/banking/reconciliations', [BankReconciliationController::class, 'store'])->name('banking.reconciliations.store');
    Route::get('/banking/reconciliations/{id}', [BankReconciliationController::class, 'show'])->name('banking.reconciliations.show');
    Route::post('/banking/reconciliations/{id}/clear-line', [BankReconciliationController::class, 'clearLine'])->name('banking.reconciliations.clear-line');
    Route::post('/banking/reconciliations/{id}/unclear-line', [BankReconciliationController::class, 'unclearLine'])->name('banking.reconciliations.unclear-line');
    Route::post('/banking/reconciliations/{id}/finalize', [BankReconciliationController::class, 'finalize'])->name('banking.reconciliations.finalize');

    // 5. Fixed Assets & Depreciation
    Route::get('/assets', [FixedAssetController::class, 'index'])->name('assets.index');
    Route::post('/assets', [FixedAssetController::class, 'store'])->name('assets.store');
    Route::get('/assets/{id}', [FixedAssetController::class, 'show'])->name('assets.show');
    Route::post('/assets/depreciate', [FixedAssetController::class, 'runDepreciation'])->name('assets.depreciate');

    // 6. Budgets & Variance Analysis
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');

    // 7. System Audit Trail
    Route::get('/audit-trail', [AuditLogController::class, 'index'])->name('audit-trail.index');

    // 8. Financial Reports (Trial Balance, P&L, Balance Sheet, General Ledger, A/R & A/P Ageing)
    Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
    Route::get('/reports/income-statement', [ReportController::class, 'incomeStatement'])->name('reports.income-statement');
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('/reports/general-ledger', [ReportController::class, 'generalLedger'])->name('reports.general-ledger');
    Route::get('/reports/ar-ageing', [ReportController::class, 'arAgeing'])->name('reports.ar-ageing');
    Route::get('/reports/ap-ageing', [ReportController::class, 'apAgeing'])->name('reports.ap-ageing');

    // 9. Accounting Settings Suite
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/taxes', [SettingsController::class, 'storeTax'])->name('settings.taxes.store');
    Route::post('/settings/taxes/{id}/toggle', [SettingsController::class, 'toggleTax'])->name('settings.taxes.toggle');
    Route::post('/settings/fiscal', [SettingsController::class, 'storeFiscalYear'])->name('settings.fiscal.store');

    // Mappings alias for backwards compatibility
    Route::get('/mappings', function () {
        return redirect()->route('fincore.settings.index', ['tab' => 'rules']);
    })->name('mappings.index');
    Route::post('/mappings/sync', [MappingController::class, 'syncDefaults'])->name('mappings.sync');
    Route::put('/mappings/{ruleId}', [MappingController::class, 'update'])->name('mappings.update');
    Route::post('/mappings/test-event', [MappingController::class, 'testEvent'])->name('mappings.test');
});
