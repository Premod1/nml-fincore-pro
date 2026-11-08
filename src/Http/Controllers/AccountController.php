<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Enums\AccountType;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Services\ChartOfAccountsInitializer;
use Nml\FinCore\Services\LedgerEngine;

class AccountController extends Controller
{
    public function index(Request $request, LedgerEngine $ledger)
    {
        $typeFilter = $request->query('type');
        $query = Account::orderBy('code');

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $accounts = $query->get();
        $balances = $ledger->getBalancesForAccounts($accounts->pluck('id')->toArray());

        return view('fincore-pro::pages.accounts.index', [
            'accounts'   => $accounts,
            'balances'   => $balances,
            'typeFilter' => $typeFilter,
            'currency'   => config('accounting.currency', 'LKR'),
            'types'      => AccountType::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:30',
            'name'    => 'required|string|max:150',
            'type'    => 'required|string',
            'subtype' => 'nullable|string|max:60',
        ]);

        Account::create([
            'code'      => $validated['code'],
            'name'      => $validated['name'],
            'type'      => $validated['type'],
            'subtype'   => $validated['subtype'] ?? $validated['type'],
            'currency'  => config('accounting.currency', 'LKR'),
            'is_active' => true,
        ]);

        return redirect()->route('fincore.accounts.index')
            ->with('fincore_success', "Account [{$validated['code']} - {$validated['name']}] created successfully.");
    }

    public function seedDefaults()
    {
        ChartOfAccountsInitializer::initialize();

        return redirect()->route('fincore.accounts.index')
            ->with('fincore_success', 'Standard Chart of Accounts initialized successfully.');
    }
}
