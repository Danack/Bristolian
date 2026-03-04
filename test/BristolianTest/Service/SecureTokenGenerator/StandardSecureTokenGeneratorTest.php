<?php

declare(strict_types = 1);

namespace BristolianTest\Service\SecureTokenGenerator;

use Bristolian\Service\SecureTokenGenerator\RandomBytesSecureTokenGenerator;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class StandardSecureTokenGeneratorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\SecureTokenGenerator\RandomBytesSecureTokenGenerator::generate
     */
    public function test_generate_returns_non_empty_url_safe_token(): void
    {
        $generator = new RandomBytesSecureTokenGenerator();

        $token = $generator->generate();

        $this->assertNotEmpty($token);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $token, 'Token should be base64url (no + or /)');
        $this->assertGreaterThanOrEqual(40, strlen($token), 'Token should be at least ~44 chars from 32 bytes base64url');
    }

    /**
     * @covers \Bristolian\Service\SecureTokenGenerator\RandomBytesSecureTokenGenerator::generate
     */
    public function test_generate_returns_different_values_each_call(): void
    {
        $generator = new RandomBytesSecureTokenGenerator();

        $token1 = $generator->generate();
        $token2 = $generator->generate();

        $this->assertNotSame($token1, $token2);
    }
}
