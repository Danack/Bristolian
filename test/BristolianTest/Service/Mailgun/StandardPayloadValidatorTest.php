<?php

declare(strict_types=1);

namespace BristolianTest\Service\Mailgun;

use Bristolian\Service\Mailgun\StandardPayloadValidator;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class StandardPayloadValidatorTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, string>}>
     */
    public static function provides_validate_returns_false_when_required_key_missing(): \Generator
    {
        yield 'signature missing' => [['timestamp' => '1234567890', 'token' => 'abc']];
        yield 'timestamp missing' => [['signature' => 'something', 'token' => 'abc']];
        yield 'token missing' => [['signature' => 'something', 'timestamp' => '1234567890']];
    }

    /**
     * @covers \Bristolian\Service\Mailgun\StandardPayloadValidator::validate
     * @dataProvider provides_validate_returns_false_when_required_key_missing
     */
    public function test_validate_returns_false_when_required_key_missing(array $payloadData): void
    {
        $payload = new ArrayVarMap($payloadData);
        $validator = new StandardPayloadValidator();
        $this->assertFalse($validator->validate($payload));
    }

    /**
     * @covers \Bristolian\Service\Mailgun\StandardPayloadValidator::validate
     */
    public function test_validate_returns_false_when_signature_does_not_match(): void
    {
        $payload = new ArrayVarMap([
            'timestamp' => '1234567890',
            'token' => 'abc',
            'signature' => 'wrong_hmac',
        ]);
        $validator = new StandardPayloadValidator();
        $this->assertFalse($validator->validate($payload));
    }

    /**
     * @covers \Bristolian\Service\Mailgun\StandardPayloadValidator::validate
     */
    public function test_validate_returns_true_when_signature_matches(): void
    {
        $timestamp = '1234567890';
        $token = 'test_token_value';
        $signingKey = getMailgunSigningKey();
        $signature = hash_hmac('sha256', $timestamp . $token, $signingKey);

        $payload = new ArrayVarMap([
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
        ]);
        $validator = new StandardPayloadValidator();
        $this->assertTrue($validator->validate($payload));
    }
}
