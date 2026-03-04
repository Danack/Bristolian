<?php

declare(strict_types = 1);

namespace Bristolian\Service\SecureTokenGenerator;

/**
 * Deterministic implementation that always returns the same token.
 * For use in tests when predictable token values are needed.
 */
class FixedSecureTokenGenerator implements SecureTokenGenerator
{
    public function __construct(
        private string $token = 'fixed-test-token'
    ) {
    }

    public function generate(): string
    {
        return $this->token;
    }
}
