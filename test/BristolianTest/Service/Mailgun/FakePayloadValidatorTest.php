<?php

declare(strict_types=1);

namespace BristolianTest\Service\Mailgun;

use Bristolian\Service\Mailgun\FakePayloadValidator;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\Mailgun\FakePayloadValidator::class, '__construct')]
#[CoversMethod(\Bristolian\Service\Mailgun\FakePayloadValidator::class, 'validate')]

class FakePayloadValidatorTest extends BaseTestCase
{
    public function test_validate_returns_true_by_default(): void
    {
        $validator = new FakePayloadValidator();
        $payload = new ArrayVarMap(['any' => 'data']);
        $this->assertTrue($validator->validate($payload));
    }

    public function test_validate_returns_false_when_constructed_with_false(): void
    {
        $validator = new FakePayloadValidator(false);
        $payload = new ArrayVarMap(['any' => 'data']);
        $this->assertFalse($validator->validate($payload));
    }
}
