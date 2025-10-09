<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Tag;
use Bristolian\Parameters\TagParams;

/**
 * @coversNothing
 */
class TagTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Tag
     */
    public function testFromParam()
    {
        $uuid = 'tag-uuid-123';
        $text = 'test-tag';
        $description = 'A test tag';

        $tagParams = new TagParams($text, $description);
        $tag = Tag::fromParam($uuid, $tagParams);

        $this->assertSame($uuid, $tag->getTagId());
        $this->assertSame($text, $tag->getText());
        $this->assertSame($description, $tag->getDescription());
    }

    /**
     * @covers \Bristolian\Model\Tag
     */
    public function testGetters()
    {
        $tagParams = new TagParams('my-tag', 'My tag description');
        $tag = Tag::fromParam('tag-id', $tagParams);

        $this->assertSame('tag-id', $tag->getTagId());
        $this->assertSame('my-tag', $tag->getText());
        $this->assertSame('My tag description', $tag->getDescription());
    }
}

