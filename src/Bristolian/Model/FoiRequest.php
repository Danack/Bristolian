<?php

namespace Bristolian\Model;

use Bristolian\DataType\FoiRequestParam;

class FoiRequest
{
    private string $foi_request_id;
    private string $text;
    private string $url;
    private string $description;

    public static function create(
        string $foi_request_id,
        string $text,
        string $url,
        string $description,
    ): self {
        $instance = new self();

        $instance->foi_request_id = $foi_request_id;
        $instance->text = $text;
        $instance->url = $url;
        $instance->description = $description;

        return $instance;
    }

    public static function fromParam(string $uuid, FoiRequestParam $foiParam): self
    {
        return self::create(
            $uuid,
            $foiParam->text,
            $foiParam->url,
            $foiParam->description
        );
    }

    public function getFoiRequestId(): string
    {
        return $this->foi_request_id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}