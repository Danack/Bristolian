<?php

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\Parameters\MemeTagDeleteParams;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\MemeTagDeleteParams
 */
class MemeTagDeleteParamTest extends BaseTestCase
{
    public function testWorks()
    {
        $meme_id = '123';
        $meme_tag_id = '12345';

        $params = [
            'meme_id' => $meme_id,
            'meme_tag_id' => $meme_tag_id,
        ];

        $deleteParam = MemeTagDeleteParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($meme_id, $deleteParam->meme_id);
        $this->assertSame($meme_tag_id, $deleteParam->meme_tag_id);
    }
}
