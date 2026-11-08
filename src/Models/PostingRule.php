<?php

namespace Nml\FinCorePro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostingRule extends Model
{
    protected $fillable = [
        'event_key',
        'name',
        'description',
        'jv_type',
        'auto_post',
        'is_active',
    ];

    protected $casts = [
        'auto_post' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getTable()
    {
        return config('accounting.table_prefix', 'fincore_') . 'posting_rules';
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PostingRuleLine::class, 'posting_rule_id')->orderBy('sort_order');
    }
}
