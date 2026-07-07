<?php

namespace KostantinoAbate\Complihance\Actions\Consent;

use Illuminate\Http\Request;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;

class ResolveCurrentConsentAction
{
    public function __construct(
        protected CurrentConsentResolver $resolver,
    ) {}

    /**
     * Resolve the current consent for the given request.
     */
    public function execute(Request $request): ?Consent
    {
        return $this->resolver->resolve($request);
    }
}
