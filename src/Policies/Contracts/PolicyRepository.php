<?php

namespace KostantinoAbate\Complihance\Policies\Contracts;

use KostantinoAbate\Complihance\DTO\Policy;

interface PolicyRepository
{
    public function current(string $key): Policy;
}
