<?php

namespace Nml\FinCorePro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nml\FinCore\Models\Account;

class PostingRuleLine extends Model
{
    protected $fillable = [
        'posting_rule_id',
        'role',
        'label',
        'type',
        'amount_key',
        'account_id',
        'default_code',
        'is_optional',
        'sort_order',
    ];

    protected $casts = [
        'is_optional' => 'boolean',
    ];

    public function getTable()
    {
        return config('accounting.table_prefix', 'fincore_') . 'posting_rule_lines';
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PostingRule::class, 'posting_rule_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
