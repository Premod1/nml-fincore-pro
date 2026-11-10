<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Enums\AccountType;
use Nml\FinCore\Exceptions\AccountingException;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Models\BankReconciliation;
use Nml\FinCore\Services\BankReconciliationEngine;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $reconciliations = BankReconciliation::with('account')
            ->orderByDesc('statement_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Bank / Cash accounts
        $bankAccounts = Account::where('is_active', true)
            ->where('type', AccountType::ASSET)
            ->where(function ($q) {
                $q->whereIn('code', ['1000', '1100'])
                  ->orWhere('subtype', 'bank')
                  ->orWhere('subtype', 'cash');
            })
            ->orderBy('code')
            ->get();

        if ($bankAccounts->isEmpty()) {
            $bankAccounts = Account::where('type', AccountType::ASSET)->where('is_active', true)->orderBy('code')->get();
        }

        return view('fincore-pro::pages.banking.reconciliations.index', [
            'reconciliations' => $reconciliations,
            'bankAccounts'    => $bankAccounts,
            'currency'        => config('accounting.currency', 'LKR'),
        ]);
    }

    public function store(Request $request, BankReconciliationEngine $engine)
    {
        $validated = $request->validate([
            'account_id'      => 'required|exists:' . (new Account())->getTable() . ',id',
            'statement_date'  => 'required|date',
            'opening_balance' => 'required|numeric',
            'closing_balance' => 'required|numeric',
        ]);

        $reconciliation = $engine->createReconciliation(
            (int) $validated['account_id'],
            $validated['statement_date'],
            (float) $validated['opening_balance'],
            (float) $validated['closing_balance']
        );

        return redirect()->route('fincore.banking.reconciliations.show', $reconciliation->id)
            ->with('fincore_success', 'Bank reconciliation session created. Review and clear matching transactions below.');
    }

    public function show($id, BankReconciliationEngine $engine)
    {
        $reconciliation = BankReconciliation::with(['account', 'lines.journalEntry'])
            ->findOrFail($id);

        $unreconciledLines = $engine->getUnreconciledLines(
            $reconciliation->account_id,
            $reconciliation->statement_date->format('Y-m-d')
        );

        $clearedBalance = $reconciliation->calculateClearedBalance();
        $difference = round($reconciliation->closing_balance - $clearedBalance, 2);

        $clearedDebits = (float) $reconciliation->lines()->where('type', 'debit')->sum('amount');
        $clearedCredits = (float) $reconciliation->lines()->where('type', 'credit')->sum('amount');

        return view('fincore-pro::pages.banking.reconciliations.show', [
            'reconciliation'    => $reconciliation,
            'unreconciledLines' => $unreconciledLines,
            'clearedBalance'    => $clearedBalance,
            'difference'        => $difference,
            'clearedDebits'     => $clearedDebits,
            'clearedCredits'    => $clearedCredits,
            'currency'          => config('accounting.currency', 'LKR'),
        ]);
    }

    public function clearLine(Request $request, $id, BankReconciliationEngine $engine)
    {
        $request->validate(['line_id' => 'required|integer']);

        try {
            $engine->manuallyClearLine((int) $id, (int) $request->line_id, now()->toDateTimeString());
            return redirect()->back()->with('fincore_success', 'Transaction line marked as cleared.');
        } catch (AccountingException $e) {
            return redirect()->back()->with('fincore_error', $e->getMessage());
        }
    }

    public function unclearLine(Request $request, $id, BankReconciliationEngine $engine)
    {
        $request->validate(['line_id' => 'required|integer']);

        try {
            $engine->unclearLine((int) $request->line_id);
            return redirect()->back()->with('fincore_success', 'Transaction line reverted to uncleared.');
        } catch (AccountingException $e) {
            return redirect()->back()->with('fincore_error', $e->getMessage());
        }
    }

    public function finalize($id, BankReconciliationEngine $engine)
    {
        try {
            $engine->finalizeReconciliation((int) $id);
            return redirect()->back()->with('fincore_success', 'Reconciliation successfully finalized and locked!');
        } catch (AccountingException $e) {
            return redirect()->back()->with('fincore_error', $e->getMessage());
        }
    }
}
