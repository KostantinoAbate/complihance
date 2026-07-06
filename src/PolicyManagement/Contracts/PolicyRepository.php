<?php

namespace KostantinoAbate\Complihance\PolicyManagement\Contracts;

use KostantinoAbate\Complihance\DTO\Policy;

interface PolicyRepository
{
    public function current(string $key): Policy;
}
