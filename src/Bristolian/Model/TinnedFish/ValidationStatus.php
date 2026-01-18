<?php

declare(strict_types = 1);

namespace Bristolian\Model\TinnedFish;

/**
 * Validation status for tinned fish products.
 */
enum ValidationStatus: string
{
    case NOT_VALIDATED = 'not_validated';
    case VALIDATED_NOT_FISH = 'validated_not_fish';
    case VALIDATED_IS_FISH = 'validated_is_fish';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::NOT_VALIDATED => 'Not Validated',
            self::VALIDATED_NOT_FISH => 'Validated - Not a Tinned Fish Product',
            self::VALIDATED_IS_FISH => 'Validated - Is a Tinned Fish Product',
        };
    }
}
