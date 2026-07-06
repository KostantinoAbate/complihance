<?php

namespace KostantinoAbate\Complihance\Actions;

use Illuminate\Http\Request;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\CurrentConsentResolver;

class ResolveCurrentConsentAction
{
    public function __construct(
        protected CurrentConsentResolver $resolver,
    ) {}

    public function execute(Request $request): ?Consent
    {
        return $this->resolver->resolve($request);
    }
}
