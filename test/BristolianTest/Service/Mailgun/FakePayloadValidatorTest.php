<?php

declare(strict_types=1);

namespace BristolianTest\Service\Mailgun;

use Bristolian\Service\Mailgun\FakePayloadValidator;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class FakePayloadValidatorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\Mailgun\FakePayloadValidator::__construct
     * @covers \Bristolian\Service\Mailgun\FakePayloadValidator::validate
     */
    public function test_validate_returns_true_by_default(): void
    {
        $validator = new FakePayloadValidator();
        $payload = new ArrayVarMap(['any' => 'data']);
        $this->assertTrue($validator->validate($payload));
    }

    /**
     * @covers \Bristolian\Service\Mailgun\FakePayloadValidator::validate
     */
    public function test_validate_returns_false_when_constructed_with_false(): void
    {
        $validator = new FakePayloadValidator(false);
        $payload = new ArrayVarMap(['any' => 'data']);
        $this->assertFalse($validator->validate($payload));
    }
}
