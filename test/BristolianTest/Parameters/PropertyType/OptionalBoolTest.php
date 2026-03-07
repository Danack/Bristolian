<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\OptionalBool;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class OptionalBoolTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, bool}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'missing key defaults to false' => [[], false];
        yield 'true string' => [['enabled' => 'true'], true];
        yield 'false string' => [['enabled' => 'false'], false];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalBool::__construct
     * @covers \Bristolian\Parameters\PropertyType\OptionalBool::getInputType
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_input_to_expected_output(array $input, bool $expectedValue): void
    {
        $params = OptionalBoolFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $params->enabled);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, bool}>
     */
    public static function provides_default_true_input_and_expected_output(): \Generator
    {
        yield 'missing key defaults to true' => [[], true];
        yield 'true string' => [['flag' => 'true'], true];
        yield 'false string' => [['flag' => 'false'], false];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalBool::__construct
     * @covers \Bristolian\Parameters\PropertyType\OptionalBool::getInputType
     * @dataProvider provides_default_true_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_default_true_parses_input_to_expected_output(array $input, bool $expectedValue): void
    {
        $params = OptionalBoolDefaultTrueFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $params->flag);
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalBool::getInputType
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new OptionalBool('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class OptionalBoolFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalBool('enabled')]
        public readonly bool $enabled,
    ) {
    }
}

class OptionalBoolDefaultTrueFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalBool('flag', default: true)]
        public readonly bool $flag,
    ) {
    }
}
