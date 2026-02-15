<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\ChatMessageParam;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class ChatMessageParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, string, int|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'all params' => [
            [
                'text' => 'Hello there.',
                'room_id' => 'room123',
                'message_reply_id' => 456,
            ],
            'Hello there.',
            'room123',
            456,
        ];
        yield 'no optional' => [
            ['text' => 'Hello there.', 'room_id' => 'room123'],
            'Hello there.',
            'room123',
            null,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\ChatMessageParam
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedText,
        string $expectedRoomId,
        ?int $expectedMessageReplyId
    ): void {
        $params = ChatMessageParam::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedText, $params->text);
        $this->assertSame($expectedRoomId, $params->room_id);
        $this->assertSame($expectedMessageReplyId, $params->message_reply_id);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, array<string, string>}>
     */
    public static function provides_invalid_input_and_expected_errors(): \Generator
    {
        yield 'missing text' => [
            ['room_id' => 'room123', 'message_reply_id' => 456],
            ['/text' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing room_id' => [
            ['text' => 'Hello there.', 'message_reply_id' => 456],
            ['/room_id' => Messages::VALUE_NOT_SET],
        ];
        yield 'invalid types' => [
            ['text' => 123, 'room_id' => 456, 'message_reply_id' => 'invalid'],
            [
                '/text' => Messages::STRING_EXPECTED,
                '/room_id' => Messages::STRING_EXPECTED,
                '/message_reply_id' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
            ],
        ];
    }

    /**
     * @covers \Bristolian\Parameters\ChatMessageParam
     * @dataProvider provides_invalid_input_and_expected_errors
     * @param array<string, mixed> $input
     * @param array<string, string> $expectedProblems
     */
    public function test_rejects_invalid_input_with_expected_errors(array $input, array $expectedProblems): void
    {
        try {
            ChatMessageParam::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems($ve->getValidationProblems(), $expectedProblems);
        }
    }
}
