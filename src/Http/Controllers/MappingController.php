<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Models\Account;
use Nml\FinCorePro\Models\PostingRule;
use Nml\FinCorePro\Models\PostingRuleLine;
use Nml\FinCorePro\Services\ErpPostingEngine;

class MappingController extends Controller
{
    public function index(ErpPostingEngine $engine)
    {
        $rules = PostingRule::with('lines.account')->orderBy('id')->get();
        if ($rules->isEmpty()) {
            $engine->syncDefaultRules();
            $rules = PostingRule::with('lines.account')->orderBy('id')->get();
        }

        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('fincore-pro::pages.mappings.index', [
            'rules'    => $rules,
            'accounts' => $accounts,
        ]);
    }

    public function syncDefaults(ErpPostingEngine $engine)
    {
        $engine->syncDefaultRules();

        return redirect()->route('fincore.mappings.index')
            ->with('fincore_success', 'Default ERP Event Posting Rules synchronized successfully.');
    }

    public function update(Request $request, int $ruleId)
    {
        $rule = PostingRule::with('lines')->findOrFail($ruleId);

        $rule->update([
            'auto_post' => $request->boolean('auto_post'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $lineAccounts = $request->input('lines', []);
        foreach ($lineAccounts as $lineId => $accountId) {
            PostingRuleLine::where('id', $lineId)
                ->where('posting_rule_id', $rule->id)
                ->update([
                    'account_id' => $accountId ?: null,
                ]);
        }

        return redirect()->route('fincore.mappings.index')
            ->with('fincore_success', "Account mappings for [{$rule->name}] updated successfully.");
    }

    public function testEvent(Request $request, ErpPostingEngine $engine)
    {
        $validated = $request->validate([
            'event_key'    => 'required|string',
            'net_amount'   => 'required|numeric|gt:0',
            'tax_amount'   => 'nullable|numeric|min:0',
            'cogs_amount'  => 'nullable|numeric|min:0',
            'reference'    => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:255',
        ]);

        try {
            $net = (float) $validated['net_amount'];
            $tax = (float) ($validated['tax_amount'] ?? 0);
            $cogs = (float) ($validated['cogs_amount'] ?? 0);

            $entry = $engine->recordErpEvent($validated['event_key'], [
                'net_amount'   => $net,
                'tax_amount'   => $tax,
                'total_amount' => $net + $tax,
                'cogs_amount'  => $cogs,
                'reference'    => $validated['reference'] ?? 'ERP-SIM-' . rand(1000, 9999),
                'description'  => $validated['description'] ?? "Simulated ERP Event ({$validated['event_key']})",
            ]);

            return redirect()->route('fincore.journals.show', $entry->id)
                ->with('fincore_success', "ERP Event [{$validated['event_key']}] automatically posted as Journal Entry {$entry->entry_number}!");
        } catch (\Throwable $e) {
            return back()->with('fincore_error', $e->getMessage());
        }
    }
}
