<?php

namespace KostantinoAbate\Complihance\Services\Policies\Repositories\Contracts;

use KostantinoAbate\Complihance\Data\Policy;

interface PolicyRepository
{
    public function current(string $key): Policy;
}
