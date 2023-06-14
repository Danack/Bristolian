<?php

namespace Bristolian\Model;

use Bristolian\DataType\TagParam;

class Tag
{
    private string $tag_id;
    private string $text;
    private string $description;

    public static function create(
        string $tag_id,
        string $text,
        string $description,
    ) {
        $instance = new self();

        $instance->tag_id = $tag_id;
        $instance->text = $text;
        $instance->description = $description;

        return $instance;
    }

    public static function fromParam(string $uuid, TagParam $tagParam)
    {
        return new self(
            $uuid,
            $tagParam->text,
            $tagParam->description
        );
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