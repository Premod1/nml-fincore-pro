<?php

namespace Nml\FinCorePro\Services;

use Illuminate\Database\Eloquent\Model;
use Nml\FinCore\Exceptions\AccountingException;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Models\JournalEntry;
use Nml\FinCore\Services\LedgerEngine;
use Nml\FinCorePro\Models\PostingRule;

class ErpPostingEngine
{
    public function __construct(
        protected LedgerEngine $ledgerEngine
    ) {}

    /**
     * Seed default ERP posting rules from config if not already seeded.
     */
    public function syncDefaultRules(): void
    {
        $defaults = config('fincore-pro.default_event_rules', []);

        foreach ($defaults as $eventKey => $ruleData) {
            $rule = PostingRule::firstOrCreate(
                ['event_key' => $eventKey],
                [
                    'name'        => $ruleData['name'],
                    'description' => $ruleData['description'] ?? null,
                    'jv_type'     => $ruleData['jv_type'] ?? 'general',
                    'auto_post'   => config('fincore-pro.auto_post_erp_events', true),
                    'is_active'   => true,
                ]
            );

            // If rule already exists, keep its name and description updated
            $rule->update([
                'name'        => $ruleData['name'],
                'description' => $ruleData['description'] ?? null,
                'jv_type'     => $ruleData['jv_type'] ?? 'general',
            ]);

            if ($rule->lines()->count() === 0 && !empty($ruleData['lines'])) {
                foreach ($ruleData['lines'] as $index => $lineDef) {
                    $account = null;
                    if (!empty($lineDef['default_code'])) {
                        $account = Account::where('code', $lineDef['default_code'])->first();
                    }

                    $rule->lines()->create([
                        'role'         => $lineDef['role'],
                        'label'        => $lineDef['label'],
                        'type'         => $lineDef['type'],
                        'amount_key'   => $lineDef['amount_key'],
                        'account_id'   => $account?->id,
                        'default_code' => $lineDef['default_code'] ?? null,
                        'is_optional'  => (bool) ($lineDef['optional'] ?? false),
                        'sort_order'   => $index + 1,
                    ]);
                }
            }
        }
    }

    /**
     * Record a high-level ERP business event (e.g., 'credit_sale', 'cash_sale', 'credit_purchase')
     * without requiring the developer to manually construct Debit/Credit journal lines.
     */
    public function recordErpEvent(string $eventKey, array $payload): JournalEntry
    {
        $rule = PostingRule::with('lines.account')
            ->where('event_key', $eventKey)
            ->where('is_active', true)
            ->first();

        if (!$rule) {
            // Attempt to sync default rules on first call
            $this->syncDefaultRules();
            $rule = PostingRule::with('lines.account')
                ->where('event_key', $eventKey)
                ->where('is_active', true)
                ->first();
        }

        if (!$rule) {
            throw new AccountingException("ERP Posting Rule [{$eventKey}] is not defined or is inactive.");
        }

        // Normalize common aliases so developers can pass 'amount'/'total' or 'net_amount'/'total_amount'
        $normalizedAmounts = $this->normalizePayloadAmounts($payload);

        $journalLines = [];

        foreach ($rule->lines as $ruleLine) {
            $amount = (float) ($normalizedAmounts[$ruleLine->amount_key] ?? 0.0);

            if ($amount <= 0) {
                if ($ruleLine->is_optional) {
                    continue;
                }
                throw new AccountingException(
                    "Missing or zero required amount [{$ruleLine->amount_key}] for ERP event [{$eventKey}] ({$ruleLine->label})."
                );
            }

            // Allow per-transaction override of account_id if needed (e.g. specific bank account)
            $overrideKey = $ruleLine->role . '_account_id';
            $accountId = $payload[$overrideKey] ?? $ruleLine->account_id;

            if (!$accountId && $ruleLine->default_code) {
                $accountId = Account::where('code', $ruleLine->default_code)->value('id');
            }

            if (!$accountId) {
                throw new AccountingException(
                    "No Ledger Account mapped for role [{$ruleLine->label}] in ERP event [{$eventKey}]. Please map it in Accounting -> ERP Mappings."
                );
            }

            $partnerModel = $payload['partner'] ?? null;

            $journalLines[] = [
                'account_id'       => $accountId,
                'type'             => $ruleLine->type,
                'amount'           => $amount,
                'description'      => $payload['description'] ?? ($rule->name . ' - ' . $ruleLine->label),
                'due_date'         => $payload['due_date'] ?? null,
                'partnerable_type' => ($partnerModel instanceof Model) ? get_class($partnerModel) : ($payload['partnerable_type'] ?? null),
                'partnerable_id'   => ($partnerModel instanceof Model) ? $partnerModel->getKey() : ($payload['partnerable_id'] ?? null),
            ];
        }

        $refModel = $payload['model'] ?? ($payload['reference'] instanceof Model ? $payload['reference'] : null);
        $refString = is_string($payload['reference'] ?? null)
            ? $payload['reference']
            : ($refModel ? class_basename($refModel) . '#' . $refModel->getKey() : null);

        $entry = $this->ledgerEngine->createJournalEntry([
            'date'             => $payload['date'] ?? now()->format('Y-m-d'),
            'reference'        => $refString,
            'type'             => $rule->jv_type,
            'description'      => $payload['description'] ?? $rule->name,
            'currency'         => $payload['currency'] ?? config('accounting.currency', 'LKR'),
            'exchange_rate'    => $payload['exchange_rate'] ?? 1.0,
            'sbu_code'         => $payload['sbu_code'] ?? null,
            'journalable_type' => $refModel ? get_class($refModel) : ($payload['journalable_type'] ?? null),
            'journalable_id'   => $refModel ? $refModel->getKey() : ($payload['journalable_id'] ?? null),
            'created_by'       => $payload['created_by'] ?? (auth()->check() ? auth()->id() : null),
            'lines'            => $journalLines,
        ]);

        $shouldAutoPost = $payload['auto_post'] ?? $rule->auto_post;
        if ($shouldAutoPost) {
            $entry->post();
        }

        return $entry;
    }

    protected function normalizePayloadAmounts(array $payload): array
    {
        $net = (float) ($payload['net_amount'] ?? $payload['amount'] ?? $payload['sub_total'] ?? 0.0);
        $tax = (float) ($payload['tax_amount'] ?? $payload['tax'] ?? $payload['vat'] ?? 0.0);
        $total = (float) ($payload['total_amount'] ?? $payload['total'] ?? $payload['grand_total'] ?? ($net + $tax));
        if ($net <= 0 && $total > 0 && $tax == 0) {
            $net = $total;
        }

        $discount = (float) ($payload['discount_amount'] ?? $payload['discount'] ?? 0.0);
        $settled = (float) ($payload['settled_amount'] ?? ($total + $discount));
        $cogs = (float) ($payload['cogs_amount'] ?? $payload['cogs'] ?? $payload['cost_amount'] ?? 0.0);
        $gross = (float) ($payload['gross_amount'] ?? $payload['gross'] ?? $total);
        $epfEtf = (float) ($payload['epf_etf_amount'] ?? $payload['epf_etf'] ?? 0.0);
        if ($net <= 0 && $gross > 0 && $epfEtf > 0) {
            $net = $gross - $epfEtf;
        }

        return array_merge($payload, [
            'net_amount'      => $net,
            'tax_amount'      => $tax,
            'total_amount'    => $total,
            'discount_amount' => $discount,
            'settled_amount'  => $settled,
            'cogs_amount'     => $cogs,
            'gross_amount'    => $gross,
            'epf_etf_amount'  => $epfEtf,
        ]);
    }
}
