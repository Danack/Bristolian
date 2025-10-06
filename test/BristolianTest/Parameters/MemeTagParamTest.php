<?php

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\Parameters\MemeTagParams;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\MemeTagParams
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

        $tagParam = MemeTagParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($meme_id, $tagParam->meme_id);
        $this->assertSame($type, $tagParam->type);
        $this->assertSame($text, $tagParam->text);
    }
}
