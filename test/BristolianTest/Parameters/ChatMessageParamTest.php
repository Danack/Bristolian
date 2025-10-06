<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\ChatMessageParam;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\ChatMessageParam
 */
class ChatMessageParamTest extends BaseTestCase
{
    public function testWorks()
    {
        $text = 'Hello there.';
        $room_id = 'room123';
        $message_reply_id = 456;

        $params = [
            'text' => $text,
            'room_id' => $room_id,
            'message_reply_id' => $message_reply_id,
        ];

        $chatMessageParam = ChatMessageParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($text, $chatMessageParam->text);
        $this->assertSame($room_id, $chatMessageParam->room_id);
        $this->assertSame($message_reply_id, $chatMessageParam->message_reply_id);
    }

    public function testWorksWithNoOptionalParameters()
    {
        $text = 'Hello there.';
        $room_id = 'room123';

        $params = [
            'text' => $text,
            'room_id' => $room_id,
        ];

        $chatMessageParam = ChatMessageParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($text, $chatMessageParam->text);
        $this->assertSame($room_id, $chatMessageParam->room_id);
        $this->assertNull($chatMessageParam->message_reply_id);
    }

    public function testFailsWithMissingText()
    {
        try {
            $params = [
                'room_id' => 'room123',
                'message_reply_id' => 456,
            ];

            ChatMessageParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/text' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingRoomId()
    {
        try {
            $params = [
                'text' => 'Hello there.',
                'message_reply_id' => 456,
            ];

            ChatMessageParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/room_id' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithInvalidDataTypes()
    {
        try {
            $params = [
                'text' => 123,
                'room_id' => 456,
                'message_reply_id' => 'invalid',
            ];

            ChatMessageParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [
                    '/text' => Messages::STRING_EXPECTED,
                    '/room_id' => Messages::STRING_EXPECTED,
                    '/message_reply_id' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2
                ]
            );
        }
    }

    public function testImplementsDataTypeInterface()
    {
        $params = [
            'text' => 'test message',
            'room_id' => 'room123',
        ];

        $chatMessageParam = ChatMessageParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\DataType\DataType::class, $chatMessageParam);
    }
}
