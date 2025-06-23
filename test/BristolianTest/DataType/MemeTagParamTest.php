<?php

namespace BristolianTest\DataType;

use Bristolian\DataType\PropertyType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\DataType\MemeTagParam;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\DataType\MemeTagParam
 */
class MemeTagParamTest extends BaseTestCase
{
    public function testWorks()
    {
        $meme_id = '123';
        $type = 'character';
        $text = 'John';

        $params = [
            'meme_id' => $meme_id,
            'type' => $type,
            'text' => $text,
        ];

        $tagParam = MemeTagParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($meme_id, $tagParam->meme_id);
        $this->assertSame($type, $tagParam->type);
        $this->assertSame($text, $tagParam->text);
    }
}
