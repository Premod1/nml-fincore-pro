<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Enums\AccountType;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Models\Budget;
use Nml\FinCore\Services\BudgetEngine;

class BudgetController extends Controller
{
    public function index(Request $request, BudgetEngine $engine)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $sbuCode = $request->query('sbu_code') ?: null;

        $varianceReport = $engine->getBudgetVarianceReport($year, $month, $sbuCode);

        $accounts = Account::where('is_active', true)
            ->whereIn('type', [AccountType::REVENUE, AccountType::EXPENSE])
            ->orderBy('code')
            ->get();

        $totalBudget = array_sum(array_column($varianceReport, 'budget_amount'));
        $totalActual = array_sum(array_column($varianceReport, 'actual_amount'));
        $totalVariance = array_sum(array_column($varianceReport, 'variance'));

        return view('fincore-pro::pages.budgets.index', [
            'varianceReport' => $varianceReport,
            'accounts'       => $accounts,
            'year'           => $year,
            'month'          => $month,
            'sbuCode'        => $sbuCode,
            'totalBudget'    => $totalBudget,
            'totalActual'    => $totalActual,
            'totalVariance'  => $totalVariance,
            'currency'       => config('accounting.currency', 'LKR'),
        ]);
    }

    public function store(Request $request, BudgetEngine $engine)
    {
        $validated = $request->validate([
            'account_id'  => 'required|exists:' . (new Account())->getTable() . ',id',
            'fiscal_year' => 'required|integer|min:2020|max:2099',
            'month'       => 'required|integer|min:1|max:12',
            'amount'      => 'required|numeric|min:0',
            'sbu_code'    => 'nullable|string|max:30',
        ]);

        $engine->setMonthlyBudget(
            (int) $validated['account_id'],
            (int) $validated['fiscal_year'],
            (int) $validated['month'],
            (float) $validated['amount'],
            $validated['sbu_code'] ?: null
        );

        return redirect()->back()
            ->with('fincore_success', 'Monthly budget target saved successfully.');
    }
}
