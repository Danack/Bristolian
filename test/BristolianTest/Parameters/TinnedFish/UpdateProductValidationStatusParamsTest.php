<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters\TinnedFish;

use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams;
use BristolianTest\BaseTestCase;
use DataType\DataType;
use DataType\Messages;
use Bristolian\StaticFactory;
use VarMap\ArrayVarMap;

/**
 * Tests for UpdateProductValidationStatusParams
 *
 * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
 */
class UpdateProductValidationStatusParamsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     */
    public function test_implements_required_interfaces(): void
    {
        $params = UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap([
            'validation_status' => ValidationStatus::NOT_VALIDATED->value,
        ]));

        $this->assertInstanceOf(DataType::class, $params);
        $this->assertInstanceOf(StaticFactory::class, $params);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     */
    public function test_works_with_not_validated(): void
    {
        $params = UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap([
            'validation_status' => ValidationStatus::NOT_VALIDATED->value,
        ]));

        $this->assertSame(ValidationStatus::NOT_VALIDATED, $params->validation_status);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     */
    public function test_works_with_validated_not_fish(): void
    {
        $params = UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap([
            'validation_status' => ValidationStatus::VALIDATED_NOT_FISH->value,
        ]));

        $this->assertSame(ValidationStatus::VALIDATED_NOT_FISH, $params->validation_status);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     */
    public function test_works_with_validated_is_fish(): void
    {
        $params = UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap([
            'validation_status' => ValidationStatus::VALIDATED_IS_FISH->value,
        ]));

        $this->assertSame(ValidationStatus::VALIDATED_IS_FISH, $params->validation_status);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     */
    public function test_fails_with_invalid_enum_value(): void
    {
        try {
            UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap([
                'validation_status' => 'invalid_status',
            ]));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/validation_status' => Messages::ENUM_MAP_UNRECOGNISED_VALUE_SINGLE]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     */
    public function test_fails_with_missing_value(): void
    {
        try {
            UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap([]));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/validation_status' => Messages::ERROR_MESSAGE_NOT_SET]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams
     */
    public function test_fails_with_null_value(): void
    {
        try {
            UpdateProductValidationStatusParams::createFromVarMap(new ArrayVarMap([
                'validation_status' => null,
            ]));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/validation_status' => Messages::STRING_EXPECTED]
            );
        }
    }
}
