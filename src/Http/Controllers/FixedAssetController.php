<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Enums\AccountType;
use Nml\FinCore\Exceptions\AccountingException;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Models\FixedAsset;
use Nml\FinCore\Services\DepreciationEngine;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = FixedAsset::with([
            'assetAccount',
            'accumulatedDepreciationAccount',
            'depreciationExpenseAccount',
            'depreciationLogs' => function ($q) {
                $q->whereHas('journalEntry', function ($query) {
                    $query->where('status', \Nml\FinCore\Enums\JvStatus::POSTED);
                });
            }
        ])->orderBy('code')->paginate(20)->withQueryString();

        $assetAccounts = Account::where('is_active', true)
            ->where('type', AccountType::ASSET)
            ->orderBy('code')
            ->get();

        $expenseAccounts = Account::where('is_active', true)
            ->where('type', AccountType::EXPENSE)
            ->orderBy('code')
            ->get();

        $totalCost = FixedAsset::where('status', 'active')->sum('purchase_cost');
        
        return view('fincore-pro::pages.assets.index', [
            'assets'          => $assets,
            'assetAccounts'   => $assetAccounts,
            'expenseAccounts' => $expenseAccounts,
            'totalCost'       => $totalCost,
            'currency'        => config('accounting.currency', 'LKR'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                                => 'required|string|max:150',
            'code'                                => 'required|string|max:50|unique:' . (new FixedAsset())->getTable() . ',code',
            'purchase_date'                       => 'required|date',
            'purchase_cost'                       => 'required|numeric|min:0.01',
            'salvage_value'                       => 'nullable|numeric|min:0',
            'useful_life_years'                   => 'required|integer|min:1',
            'depreciation_method'                 => 'required|in:straight_line,reducing_balance',
            'depreciation_rate'                   => 'nullable|numeric|min:0|max:100',
            'asset_account_id'                    => 'required|exists:' . (new Account())->getTable() . ',id',
            'accumulated_depreciation_account_id' => 'required|exists:' . (new Account())->getTable() . ',id',
            'depreciation_expense_account_id'     => 'required|exists:' . (new Account())->getTable() . ',id',
        ]);

        $usefulLife = (int) $validated['useful_life_years'];
        $rate = !empty($validated['depreciation_rate'])
            ? (float) $validated['depreciation_rate']
            : ($usefulLife > 0 ? (100 / $usefulLife) : 0);

        FixedAsset::create([
            'name'                                => $validated['name'],
            'code'                                => $validated['code'],
            'purchase_date'                       => $validated['purchase_date'],
            'purchase_cost'                       => (float) $validated['purchase_cost'],
            'salvage_value'                       => (float) ($validated['salvage_value'] ?? 0),
            'useful_life_years'                   => $usefulLife,
            'depreciation_method'                 => $validated['depreciation_method'],
            'depreciation_rate'                   => round($rate, 2),
            'asset_account_id'                    => (int) $validated['asset_account_id'],
            'accumulated_depreciation_account_id' => (int) $validated['accumulated_depreciation_account_id'],
            'depreciation_expense_account_id'     => (int) $validated['depreciation_expense_account_id'],
            'status'                              => 'active',
        ]);

        return redirect()->route('fincore.assets.index')
            ->with('fincore_success', "Fixed asset {$validated['name']} ({$validated['code']}) registered successfully.");
    }

    public function show($id, DepreciationEngine $engine)
    {
        $asset = FixedAsset::with([
            'assetAccount',
            'accumulatedDepreciationAccount',
            'depreciationExpenseAccount',
            'depreciationLogs.journalEntry.lines'
        ])->findOrFail($id);

        $accumulated = (float) $asset->depreciationLogs
            ->filter(fn($log) => $log->journalEntry?->status === \Nml\FinCore\Enums\JvStatus::POSTED)
            ->sum('amount');

        $bookValue = max(0, $asset->purchase_cost - $accumulated);
        $nextMonthDepreciation = $engine->calculateMonthlyDepreciation($asset, now()->format('Y-m-d'));

        return view('fincore-pro::pages.assets.show', [
            'asset'                 => $asset,
            'accumulated'           => $accumulated,
            'bookValue'             => $bookValue,
            'nextMonthDepreciation' => $nextMonthDepreciation,
            'currency'              => config('accounting.currency', 'LKR'),
        ]);
    }

    public function runDepreciation(Request $request, DepreciationEngine $engine)
    {
        $validated = $request->validate([
            'date'     => 'required|date',
            'asset_id' => 'nullable|integer',
        ]);

        $date = $validated['date'];

        try {
            if (!empty($validated['asset_id'])) {
                $entry = $engine->postDepreciationForAsset((int) $validated['asset_id'], $date);
                if ($entry) {
                    return redirect()->back()
                        ->with('fincore_success', "Monthly depreciation posted successfully via Journal Voucher #{$entry->entry_number}.");
                }
                return redirect()->back()
                    ->with('fincore_error', "Asset is already fully depreciated or depreciation amount is zero.");
            }

            // Run batch for all active assets
            $results = $engine->postDepreciationForAllActiveAssets($date);
            $successCount = 0;
            $skippedCount = 0;
            $failedCount = 0;

            foreach ($results as $res) {
                if ($res['status'] === 'success') $successCount++;
                elseif ($res['status'] === 'skipped') $skippedCount++;
                elseif ($res['status'] === 'failed') $failedCount++;
            }

            return redirect()->back()
                ->with('fincore_success', "Depreciation batch executed for {$date}: {$successCount} posted, {$skippedCount} skipped (fully depreciated), {$failedCount} failed.");

        } catch (AccountingException $e) {
            return redirect()->back()->with('fincore_error', $e->getMessage());
        }
    }
}
