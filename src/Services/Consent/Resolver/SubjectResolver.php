<?php

namespace KostantinoAbate\Complihance\Services\Consent\Resolver;

use Illuminate\Contracts\Auth\Authenticatable;

class SubjectResolver
{
    /**
     * Resolve the authenticated subject for the current request.
     */
    public function resolve(): ?Authenticatable
    {
        return auth()->check() ? auth()->user() : null;
    }

    /**
     * Resolve the morph class for the given subject.
     */
    public function type(mixed $subject): ?string
    {
        return $subject ? $subject->getMorphClass() : null;
    }

    /**
     * Resolve the primary key for the given subject.
     */
    public function id(mixed $subject): int|string|null
    {
        return $subject?->getKey();
    }
}
