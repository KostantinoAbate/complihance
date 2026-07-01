<?php

namespace KostantinoAbate\Complihance\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \KostantinoAbate\Complihance\ComplihancePolicy
 */
class ComplihancePolicy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'complihance.policy';
    }
}
