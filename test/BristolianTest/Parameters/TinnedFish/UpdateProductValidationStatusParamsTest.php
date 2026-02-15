<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters\TinnedFish;

use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * Tests for UpdateProductValidationStatusParams
 *
 * @coversNothing
 */
class UpdateProductValidationStatusParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, ValidationStatus}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'not_validated' => [
            ['validation_status' => ValidationStatus::NOT_VALIDATED->value],
            ValidationStatus::NOT_VALIDATED,
        ];
        yield 'validated_not_fish' => [
            ['validation_status' => ValidationStatus::VALIDATED_NOT_FISH->value],
            ValidationStatus::VALIDATED_NOT_FISH,
        ];
        yield 'validated_is_fish' => [
            ['validation_status' => ValidationStatus::VALIDATED_IS_FISH->value],
            ValidationStatus::VALIDATED_IS_FISH,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     * @covers \Bristolian\Parameters\PropertyType\BasicPhpEnumType
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        ValidationStatus $expectedStatus
    ): void {
        $params = UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedStatus, $params->validation_status);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'invalid enum value' => [
            ['validation_status' => 'invalid_status'],
            '/validation_status',
            Messages::ENUM_MAP_UNRECOGNISED_VALUE_SINGLE,
        ];
        yield 'missing value' => [
            [],
            '/validation_status',
            Messages::ERROR_MESSAGE_NOT_SET,
        ];
        yield 'null value' => [
            ['validation_status' => null],
            '/validation_status',
            Messages::STRING_EXPECTED,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     * @covers \Bristolian\Parameters\PropertyType\BasicPhpEnumType
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $errorPath,
        string $expectedErrorMessage
    ): void {
        try {
            UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [$errorPath => $expectedErrorMessage]
            );
        }
    }
}
