<?php

namespace KostantinoAbate\Complihance\Services\Policies\Repositories\Contracts;

use KostantinoAbate\Complihance\Data\Policy;

interface PolicyRepository
{
    /**
     * Retrieve the current policy version for the given policy key.
     */
    public function current(string $key): Policy;
}
