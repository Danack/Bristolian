<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\OptionalBasicString;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class OptionalBasicStringTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'with value' => [['optional_input' => 'some value'], 'some value'];
        yield 'missing' => [[], null];
        yield 'empty string' => [['optional_input' => ''], ''];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalBasicString
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $paramTest = OptionalBasicStringFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalBasicString
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new OptionalBasicString('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class OptionalBasicStringFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalBasicString('optional_input')]
        public readonly ?string $value,
    ) {
    }
}
