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
 * @covers \Bristolian\Parameters\PropertyType\ChatMessageReplyId
 */
class ChatMessageReplyIdTest extends BaseTestCase
{
    public function testWorksWithValue()
    {
        $value = 123;
        $data = ['reply_id_input' => $value];

        $replyIdParamTest = ChatMessageReplyIdFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $replyIdParamTest->value);
    }

    public function testFailsWithNull()
    {
        try {
            $data = ['reply_id_input' => null];

            ChatMessageReplyIdFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/reply_id_input' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE]
            );
        }
    }

    public function testWorksWithMissingValue()
    {
        $data = [];

        $replyIdParamTest = ChatMessageReplyIdFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($replyIdParamTest->value);
    }

    public function testWorksWithStringInteger()
    {
        $value = '456';
        $data = ['reply_id_input' => $value];

        $replyIdParamTest = ChatMessageReplyIdFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame(456, $replyIdParamTest->value);
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new ChatMessageReplyId('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new ChatMessageReplyId('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
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
