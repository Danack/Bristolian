<?php

declare(strict_types=1);

namespace Bristolian\Model\TinnedFish;

use Bristolian\ToArray;

/**
 * Copyright attribution information for external data sources.
 * Required when data is fetched from external APIs like OpenFoodFacts.
 */
class Copyright
{
    use ToArray;

    public function __construct(
        public readonly string $notice,
        public readonly string $owner,
        public readonly string $source,
        public readonly ?string $license,
        public readonly bool $attribution_required
    ) {
    }

    /**
     * Create the standard OpenFoodFacts copyright attribution.
     */
    public static function openFoodFacts(): self
    {
        return new self(
            notice: 'Data subject to copyright',
            owner: 'OpenFoodFacts contributors',
            source: 'OpenFoodFacts',
            license: 'ODbL 1.0',
            attribution_required: true
        );
    }
}
