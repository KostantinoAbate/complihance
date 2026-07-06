<?php

namespace KostantinoAbate\Complihance\Data;

class Policy
{
    public function __construct(
        public readonly string $key,
        public readonly string $version,
        public readonly string $title,
        public readonly ?string $content = null,
        public readonly ?string $view = null,
        public readonly ?string $driver = null,
    ) {}

    public function isBlade(): bool
    {
        return filled($this->view);
    }
}
