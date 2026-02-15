<?php

declare(strict_types = 1);

namespace BristolianTest\Model\TinnedFish;

use Bristolian\Model\TinnedFish\ValidationStatus;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ValidationStatusTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{ValidationStatus, string}>
     */
    public static function provides_status_and_display_name(): \Generator
    {
        yield 'not validated' => [ValidationStatus::NOT_VALIDATED, 'Not Validated'];
        yield 'validated not fish' => [ValidationStatus::VALIDATED_NOT_FISH, 'Validated - Not a Tinned Fish Product'];
        yield 'validated is fish' => [ValidationStatus::VALIDATED_IS_FISH, 'Validated - Is a Tinned Fish Product'];
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\ValidationStatus::getDisplayName
     * @dataProvider provides_status_and_display_name
     */
    public function test_getDisplayName_returns_expected_string(ValidationStatus $status, string $expectedDisplayName): void
    {
        $this->assertSame($expectedDisplayName, $status->getDisplayName());
    }
}
