<?php

namespace KostantinoAbate\Complihance\Facades;

use Illuminate\Support\Facades\Facade;
use KostantinoAbate\Complihance\Data\Policy;

/**
 * @method static Policy get(string $key)
 * @method static Policy privacy()
 * @method static Policy cookie()
 * @method static string currentVersion(string $key)
 * @method static string|null currentContent(string $key)
 * @method static array<int, string> configuredKeys()
 * @method static bool hasAccepted(string $key, mixed $subject = null, ?string $source = null, ?string $anonymousId = null, ?string $sessionId = null)
 * @method static bool requiresAcceptance(string $key, mixed $subject = null, ?string $source = null, ?string $anonymousId = null, ?string $sessionId = null)
 *
 * @see \KostantinoAbate\Complihance\Services\Policies\PolicyManager
 */
class ComplihancePolicy extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'complihance.policy';
    }
}
