<?php

namespace KostantinoAbate\Complihance\Services\Consent\Resolver;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnonymousIdResolver
{
    public function resolve(Request $request): string
    {
        return $request->cookie(
            config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')
        ) ?? (string) Str::uuid();
    }
}
