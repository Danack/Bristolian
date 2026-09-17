<?php

declare(strict_types = 1);

namespace BristolianTest\Service\SecureTokenGenerator;

use Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator::class, '__construct')]
#[CoversMethod(\Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator::class, 'generate')]

class FakeSecureTokenGeneratorTest extends BaseTestCase
{
    public function test_generate_returns_configured_token(): void
    {
        $token = 'my-fixed-token-value';
        $generator = new FixedSecureTokenGenerator($token);

        $this->assertSame($token, $generator->generate());
        $this->assertSame($token, $generator->generate());
    }

    public function test_generate_returns_default_token_when_none_configured(): void
    {
        $generator = new FixedSecureTokenGenerator();

        $this->assertSame('fixed-test-token', $generator->generate());
    }
}
