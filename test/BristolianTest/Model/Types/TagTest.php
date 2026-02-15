<?php

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\Tag;
use Bristolian\Parameters\TagParams;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class TagTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\Tag
     */
    public function testFromParam(): void
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
     * @covers \Bristolian\Model\Types\Tag
     */
    public function testFromRow(): void
    {
        $row = [
            'tag_id' => 'tag-from-row-123',
            'text' => 'row-tag-text',
            'description' => 'Row tag description',
        ];

        $tag = Tag::fromRow($row);

        $this->assertSame('tag-from-row-123', $tag->getTagId());
        $this->assertSame('row-tag-text', $tag->getText());
        $this->assertSame('Row tag description', $tag->getDescription());
    }

    /**
     * @covers \Bristolian\Model\Types\Tag
     */
    public function testGetters(): void
    {
        $tagParams = new TagParams('my-tag', 'My tag description');
        $tag = Tag::fromParam('tag-id', $tagParams);

        $this->assertSame('tag-id', $tag->getTagId());
        $this->assertSame('my-tag', $tag->getText());
        $this->assertSame('My tag description', $tag->getDescription());
    }
}
