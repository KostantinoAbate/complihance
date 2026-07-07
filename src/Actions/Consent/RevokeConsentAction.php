<?php

namespace KostantinoAbate\Complihance\Actions\Consent;

use Illuminate\Http\Request;

class RevokeConsentAction
{
    public function __construct(
        protected ResolveCurrentConsentAction $resolveCurrentConsent,
    ) {}

    /**
     * Revoke the current consent associated with the given request.
     */
    public function execute(Request $request): void
    {
        $this->resolveCurrentConsent
            ->execute($request)
            ?->update([
                'revoked_at' => now(),
            ]);
    }
}
