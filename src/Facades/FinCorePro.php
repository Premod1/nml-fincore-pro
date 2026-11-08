<?php

namespace Nml\FinCorePro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Nml\FinCore\Models\JournalEntry recordErpEvent(string $eventKey, array $payload)
 * @method static void syncDefaultRules()
 *
 * @see \Nml\FinCorePro\Services\ErpPostingEngine
 */
class FinCorePro extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fincore-pro';
    }
}
