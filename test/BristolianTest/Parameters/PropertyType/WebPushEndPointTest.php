<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\WebPushEndPoint;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class WebPushEndPointTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            ['endpoint_input' => 'https://fcm.googleapis.com/fcm/send/example-token'],
            'https://fcm.googleapis.com/fcm/send/example-token',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\WebPushEndPoint
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedValue): void
    {
        $paramTest = WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'empty string' => [['endpoint_input' => ''], Messages::STRING_TOO_SHORT];
        yield 'too long' => [['endpoint_input' => 'https://example.com/' . str_repeat('a', 600)], Messages::STRING_TOO_LONG];
        yield 'non-https url' => [['endpoint_input' => 'http://example.com/endpoint'], Messages::STRING_REQUIRES_PREFIX];
        yield 'invalid type' => [['endpoint_input' => 123], Messages::STRING_EXPECTED];
        yield 'null value' => [['endpoint_input' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\WebPushEndPoint
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/endpoint_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\WebPushEndPoint
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new WebPushEndPoint('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class WebPushEndPointFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[WebPushEndPoint('endpoint_input')]
        public readonly string $value,
    ) {
    }
}
