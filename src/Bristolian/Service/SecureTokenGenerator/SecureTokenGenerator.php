<?php

declare(strict_types = 1);

namespace Bristolian\Service\SecureTokenGenerator;

/**
 * Generates a cryptographically secure token string.
 * Implementations may be real (random) or deterministic (for tests).
 */
interface SecureTokenGenerator
{
    public function generate(): string;
}
