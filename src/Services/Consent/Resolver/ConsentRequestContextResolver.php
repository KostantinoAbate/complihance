<?php

namespace KostantinoAbate\Complihance\Services\Consent\Resolver;

use Illuminate\Http\Request;
use KostantinoAbate\Complihance\Data\ConsentRequestContext;

class ConsentRequestContextResolver
{
    public function __construct(
        protected AnonymousIdResolver $anonymousIdResolver,
        protected SubjectResolver $subjectResolver,
    ) {}

    public function resolve(Request $request): ConsentRequestContext
    {
        $subject = $this->subjectResolver->resolve();

        return new ConsentRequestContext(
            sessionId: $request->hasSession() ? $request->session()->getId() : null,
            anonymousId: $this->anonymousIdResolver->resolve($request),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            subjectType: $this->subjectResolver->type($subject),
            subjectId: $this->subjectResolver->id($subject),
            subject: $subject,
            isSecure: $request->isSecure(),
        );
    }
}
