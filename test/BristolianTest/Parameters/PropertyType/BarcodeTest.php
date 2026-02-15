<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\Barcode;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class BarcodeTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield '8 digit' => [['barcode_input' => '12345678'], '12345678'];
        yield '13 digit' => [['barcode_input' => '1234567890123'], '1234567890123'];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\Barcode
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedValue): void
    {
        $paramTest = BarcodeFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'too short' => [['barcode_input' => '1234567'], Messages::STRING_TOO_SHORT];
        yield 'too long' => [['barcode_input' => '12345678901234'], Messages::STRING_TOO_LONG];
        yield 'non-numeric' => [['barcode_input' => '12345678a'], Messages::STRING_FOUND_INVALID_CHAR];
        yield 'null value' => [['barcode_input' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\Barcode
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            BarcodeFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/barcode_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\Barcode
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new Barcode('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class BarcodeFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[Barcode('barcode_input')]
        public readonly string $value,
    ) {
    }
}
