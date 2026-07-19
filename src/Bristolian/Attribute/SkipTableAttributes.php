<?php

declare(strict_types=1);

namespace Bristolian\Attribute;

use Attribute;

/**
 * Opt out of ReadsTable / WritesTable consistency checking for a class or interface
 * that lives under an enforced directory.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SkipTableAttributes
{
    public function __construct(
        public readonly string $reason = ''
    ) {
    }
}
