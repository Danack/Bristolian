<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\SourceLinkPage;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class SourceLinkPageTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, int}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'integer' => [['page_input' => 5], 5];
        yield 'string integer' => [['page_input' => '10'], 10];
        yield 'zero' => [['page_input' => 0], 0];
        yield 'max value' => [['page_input' => 1000], 1000];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkPage
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, int $expectedValue): void
    {
        $paramTest = SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'negative' => [['page_input' => -1], Messages::INT_TOO_SMALL];
        yield 'too high' => [['page_input' => 1001], Messages::INT_TOO_LARGE];
        yield 'invalid type' => [['page_input' => 'not a number'], Messages::INT_REQUIRED_FOUND_NON_DIGITS2];
        yield 'null value' => [['page_input' => null], Messages::INT_REQUIRED_UNSUPPORTED_TYPE];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkPage
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/page_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkPage
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new SourceLinkPage('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class SourceLinkPageFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[SourceLinkPage('page_input')]
        public readonly int $value,
    ) {
    }
}
