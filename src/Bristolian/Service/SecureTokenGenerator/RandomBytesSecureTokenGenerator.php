<?php

declare(strict_types = 1);

namespace Bristolian\Service\SecureTokenGenerator;

use function generateSecureToken;

/**
 * Standard implementation that delegates to generateSecureToken().
 */
class RandomBytesSecureTokenGenerator implements SecureTokenGenerator
{
    public function generate(): string
    {
        return generateSecureToken();
    }
}
