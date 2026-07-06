<?php

namespace KostantinoAbate\Complihance\Actions;

use Illuminate\Http\Request;

class RevokeConsentAction
{
    public function __construct(
        protected ResolveCurrentConsentAction $resolveCurrentConsent,
    ) {}

    public function execute(Request $request): void
    {
        $this->resolveCurrentConsent
            ->execute($request)
            ?->update([
                'revoked_at' => now(),
            ]);
    }
}
