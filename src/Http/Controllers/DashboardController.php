<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Routing\Controller;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Models\JournalEntry;
use Nml\FinCore\Services\LedgerEngine;

class DashboardController extends Controller
{
    public function index(LedgerEngine $ledger)
    {
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $pnl = $ledger->getIncomeStatement($startOfMonth, $today);
        $bs = $ledger->getBalanceSheet($today);
        $tb = $ledger->getTrialBalance(null, $today);

        $recentEntries = JournalEntry::with('lines.account')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $accountsCount = Account::count();

        return view('fincore-pro::pages.dashboard', [
            'pnl'           => $pnl,
            'bs'            => $bs,
            'tb'            => $tb,
            'recentEntries' => $recentEntries,
            'accountsCount' => $accountsCount,
            'currency'      => config('accounting.currency', 'LKR'),
        ]);
    }
}
