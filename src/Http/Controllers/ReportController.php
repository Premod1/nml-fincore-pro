<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Services\LedgerEngine;

class ReportController extends Controller
{
    public function trialBalance(Request $request, LedgerEngine $ledger)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $sbuCode = $request->query('sbu_code');

        $report = $ledger->getTrialBalance($startDate, $endDate, $sbuCode);

        return view('fincore-pro::pages.reports.trial-balance', [
            'report'    => $report,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'sbuCode'   => $sbuCode,
            'currency'  => config('accounting.currency', 'LKR'),
        ]);
    }

    public function incomeStatement(Request $request, LedgerEngine $ledger)
    {
        $startDate = $request->query('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $sbuCode = $request->query('sbu_code');

        $report = $ledger->getIncomeStatement($startDate, $endDate, $sbuCode);

        return view('fincore-pro::pages.reports.income-statement', [
            'report'    => $report,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'sbuCode'   => $sbuCode,
            'currency'  => config('accounting.currency', 'LKR'),
        ]);
    }

    public function balanceSheet(Request $request, LedgerEngine $ledger)
    {
        $asOfDate = $request->query('as_of_date', now()->format('Y-m-d'));
        $sbuCode = $request->query('sbu_code');

        $report = $ledger->getBalanceSheet($asOfDate, $sbuCode);

        return view('fincore-pro::pages.reports.balance-sheet', [
            'report'   => $report,
            'asOfDate' => $asOfDate,
            'sbuCode'  => $sbuCode,
            'currency' => config('accounting.currency', 'LKR'),
        ]);
    }

    public function generalLedger(Request $request, LedgerEngine $ledger)
    {
        $accountId = $request->filled('account_id') ? (int) $request->query('account_id') : null;
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));

        $report = $ledger->getGeneralLedger($accountId, $startDate, $endDate);
        $allAccounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('fincore-pro::pages.reports.general-ledger', [
            'report'      => $report,
            'allAccounts' => $allAccounts,
            'accountId'   => $accountId,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'currency'    => config('accounting.currency', 'LKR'),
        ]);
    }
}
