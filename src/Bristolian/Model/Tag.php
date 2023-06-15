<?php

namespace Bristolian\Model;

use Bristolian\DataType\TagParam;

class Tag
{
    private string $tag_id;
    private string $text;
    private string $description;

    public static function fromParam(string $uuid, TagParam $tagParam): self
    {
        $instance = new self();

        $instance->tag_id = $uuid;
        $instance->text = $tagParam->text;
        $instance->description = $tagParam->description;

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
