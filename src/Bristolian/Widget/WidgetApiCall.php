<?php

declare(strict_types=1);

namespace Bristolian\Widget;

/**
 * Declared API edge used by a widget. method + path must match an api_routes entry.
 */
final readonly class WidgetApiCall
{
    public function __construct(
        public string $method,
        public string $path,
    ) {
    }

    public function routeKey(): string
    {
        return $this->method . ' ' . $this->path;
    }
}
