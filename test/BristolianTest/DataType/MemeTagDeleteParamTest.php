<?php

namespace BristolianTest\DataType;

use Bristolian\DataType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\DataType\MemeTagDeleteParam;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\DataType\MemeTagDeleteParam
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

        $deleteParam = MemeTagDeleteParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($meme_id, $deleteParam->meme_id);
        $this->assertSame($meme_tag_id, $deleteParam->meme_tag_id);
    }
}
