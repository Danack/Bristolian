<?php


namespace Bristolian\Model\Types;

use Bristolian\Parameters\TagParams;

class Tag
{
    private string $tag_id;
    private string $text;
    private string $description;

    public static function fromParam(string $uuid, TagParams $tagParam): self
    {
        $instance = new self();

        $instance->tag_id = $uuid;
        $instance->text = $tagParam->text;
        $instance->description = $tagParam->description;

        return $instance;
    }

    /**
     * @param array{tag_id: string, text: string, description: string} $row
     */
    public static function fromRow(array $row): self
    {
        $instance = new self();
        $instance->tag_id = $row['tag_id'];
        $instance->text = $row['text'];
        $instance->description = $row['description'];
        return $instance;
    }

    public function getTagId(): string
    {
        return $this->tag_id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
