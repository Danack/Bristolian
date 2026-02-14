<?php

namespace Bristolian\Model\Types;

use Bristolian\Parameters\FoiRequestParams;

class FoiRequest
{

    public function __construct(
        private string $foi_request_id,
        private string $text,
        private string $url,
        private string $description,
        private \DateTimeInterface $created_at,
    ) {
    }


//    public static function create(
//        string $foi_request_id,
//        string $text,
//        string $url,
//        string $description,
//    ): self {
//        $instance = new self();
//
//        $instance->foi_request_id = $foi_request_id;
//        $instance->text = $text;
//        $instance->url = $url;
//        $instance->description = $description;
//
//        return $instance;
//    }

    public static function fromParam(string $uuid, FoiRequestParams $foiParam): self
    {
        return new self(
            $uuid,
            $foiParam->text,
            $foiParam->url,
            $foiParam->description,
            created_at: new \DateTimeImmutable(),
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

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }
}
