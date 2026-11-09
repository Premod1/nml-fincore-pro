<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Models\FiscalYear;
use Nml\FinCore\Models\Tax;
use Nml\FinCorePro\Models\PostingRule;
use Nml\FinCorePro\Services\ErpPostingEngine;

class SettingsController extends Controller
{
    public function index(Request $request, ErpPostingEngine $engine)
    {
        $tab = $request->query('tab', 'rules');

        // Rules & Mappings
        $rules = PostingRule::with('lines.account')->orderBy('id')->get();
        if ($rules->isEmpty()) {
            $engine->syncDefaultRules();
            $rules = PostingRule::with('lines.account')->orderBy('id')->get();
        }

        // Taxes
        $taxes = Tax::with('account')->orderBy('id')->get();

        // Fiscal Years
        $fiscalYears = FiscalYear::with('periods')->orderByDesc('start_date')->get();

        // Accounts for dropdowns
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('fincore-pro::pages.settings.index', [
            'tab'         => $tab,
            'rules'       => $rules,
            'taxes'       => $taxes,
            'fiscalYears' => $fiscalYears,
            'accounts'    => $accounts,
            'currency'    => config('accounting.currency', 'LKR'),
        ]);
    }

    public function storeTax(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => 'required|string|max:20',
            'rate'       => 'required|numeric|min:0|max:100',
            'account_id' => 'required|integer',
        ]);

        Tax::create([
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'rate'       => $validated['rate'],
            'account_id' => $validated['account_id'],
            'is_active'  => true,
        ]);

        return redirect()->route('fincore.settings.index', ['tab' => 'taxes'])
            ->with('fincore_success', "Tax rate [{$validated['name']} ({$validated['rate']}%) ] created successfully.");
    }

    public function toggleTax(int $id)
    {
        $tax = Tax::findOrFail($id);
        $tax->update(['is_active' => !$tax->is_active]);

        return redirect()->route('fincore.settings.index', ['tab' => 'taxes'])
            ->with('fincore_success', "Tax [{$tax->name}] status updated.");
    }

    public function storeFiscalYear(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $fy = FiscalYear::create([
            'name'       => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'is_current' => true,
            'is_closed'  => false,
        ]);

        // Auto generate 12 monthly fiscal periods
        $start = new \DateTime($validated['start_date']);
        $end = new \DateTime($validated['end_date']);
        $periodNum = 1;

        while ($start < $end) {
            $periodStart = clone $start;
            $periodEnd = (clone $start)->modify('last day of this month');
            if ($periodEnd > $end) {
                $periodEnd = clone $end;
            }

            $fy->periods()->create([
                'name'          => $periodStart->format('F Y'),
                'period_number' => $periodNum++,
                'start_date'    => $periodStart->format('Y-m-d'),
                'end_date'      => $periodEnd->format('Y-m-d'),
                'is_closed'     => false,
            ]);

            $start->modify('first day of next month');
        }

        return redirect()->route('fincore.settings.index', ['tab' => 'fiscal'])
            ->with('fincore_success', "Fiscal Year [{$validated['name']}] and 12 monthly periods created successfully.");
    }
}
