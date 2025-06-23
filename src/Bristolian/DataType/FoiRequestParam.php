<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\BasicString;
use Bristolian\DataType\PropertyType\Url;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class FoiRequestParam implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('text')]
        public string $text,
        #[Url('url')]
        public string $url,
        #[BasicString('description')]
        public string $description,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
