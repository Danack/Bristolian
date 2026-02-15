<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\ChatMessageReplyId;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class ChatMessageReplyIdTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, int|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'integer' => [['reply_id_input' => 123], 123];
        yield 'missing' => [[], null];
        yield 'string integer' => [['reply_id_input' => '456'], 456];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ChatMessageReplyId
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?int $expectedValue): void
    {
        $paramTest = ChatMessageReplyIdFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'null value' => [['reply_id_input' => null], Messages::INT_REQUIRED_UNSUPPORTED_TYPE];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ChatMessageReplyId
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            ChatMessageReplyIdFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/reply_id_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ChatMessageReplyId
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new ChatMessageReplyId('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class ChatMessageReplyIdFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[ChatMessageReplyId('reply_id_input')]
        public readonly ?int $value,
    ) {
    }
}
