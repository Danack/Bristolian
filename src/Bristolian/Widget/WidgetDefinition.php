<?php

declare(strict_types=1);

namespace Bristolian\Widget;

/**
 * Registration metadata for a frontend widgety panel.
 */
final readonly class WidgetDefinition
{
    /**
     * @param list<WidgetApiCall> $apiCalls
     */
    public function __construct(
        public string $cssClass,
        public string $exportName,
        public string $modulePath,
        public array $apiCalls = [],
    ) {
    }
}
