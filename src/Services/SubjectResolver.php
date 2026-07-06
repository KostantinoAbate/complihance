<?php

namespace KostantinoAbate\Complihance\Services;

class SubjectResolver
{
    public function resolve(): mixed
    {
        return auth()->check() ? auth()->user() : null;
    }

    public function type(mixed $subject): ?string
    {
        return $subject ? $subject->getMorphClass() : null;
    }

    public function id(mixed $subject): mixed
    {
        return $subject?->getKey();
    }
}
