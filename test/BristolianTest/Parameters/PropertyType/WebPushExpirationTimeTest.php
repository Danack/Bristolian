<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\WebPushExpirationTime;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class WebPushExpirationTimeTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'with value' => [['expiration_input' => '2023-12-25T14:30:00Z'], '2023-12-25T14:30:00Z'];
        yield 'null' => [['expiration_input' => null], null];
        yield 'empty string' => [['expiration_input' => ''], ''];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\WebPushExpirationTime
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $paramTest = WebPushExpirationTimeFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\WebPushExpirationTime
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            WebPushExpirationTimeFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/expiration_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\WebPushExpirationTime
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new WebPushExpirationTime('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class WebPushExpirationTimeFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[WebPushExpirationTime('expiration_input')]
        public readonly ?string $value,
    ) {
    }
}
