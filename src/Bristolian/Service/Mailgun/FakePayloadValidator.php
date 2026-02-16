<?php

declare(strict_types=1);

namespace Bristolian\Service\Mailgun;

use VarMap\VarMap;

/**
 * Fake implementation of PayloadValidator for testing.
 */
class FakePayloadValidator implements PayloadValidator
{
    public function __construct(
        private bool $validateResult = true
    ) {
    }

    public function validate(VarMap $payload): bool
    {
        return $this->validateResult;
    }
}
