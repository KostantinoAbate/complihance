<?php

namespace KostantinoAbate\Complihance\Data;

/**
 * Immutable policy definition resolved from package configuration.
 */
readonly class Policy
{
    public function __construct(
        public string  $key,
        public string  $version,
        public string  $title,
        public ?string $content = null,
        public ?string $view = null,
        public ?string $driver = null,
    ) {}

    /**
     * Determine whether the policy should be rendered from a Blade view.
     *
     * @noinspection PhpUnused
     */
    public function isBlade(): bool
    {
        return $this->driver === 'blade' || filled($this->view);
    }
}
