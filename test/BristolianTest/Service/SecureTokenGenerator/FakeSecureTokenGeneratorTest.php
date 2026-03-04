<?php

declare(strict_types = 1);

namespace BristolianTest\Service\SecureTokenGenerator;

use Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeSecureTokenGeneratorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator::__construct
     * @covers \Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator::generate
     */
    public function test_generate_returns_configured_token(): void
    {
        $token = 'my-fixed-token-value';
        $generator = new FixedSecureTokenGenerator($token);

        $this->assertSame($token, $generator->generate());
        $this->assertSame($token, $generator->generate());
    }

    /**
     * @covers \Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator::__construct
     * @covers \Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator::generate
     */
    public function test_generate_returns_default_token_when_none_configured(): void
    {
        $generator = new FixedSecureTokenGenerator();

        $this->assertSame('fixed-test-token', $generator->generate());
    }
}
