<?php

namespace KostantinoAbate\Complihance\Data;

/**
 * Immutable context extracted from the current consent request.
 */
readonly class ConsentRequestContext
{
    public function __construct(
        public ?string $sessionId,
        public ?string $anonymousId,
        public ?string $ipAddress,
        public ?string $userAgent,
        public ?string $subjectType,
        public mixed $subjectId,
        public mixed $subject,
        public bool $isSecure,
    ) {}
}
