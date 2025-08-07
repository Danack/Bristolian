<?php

namespace BristolianTest\PdoSimple;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\PropertyType\SourceLinkPositionValue;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class PdoSimpleTestObjectProperties
{
    public int $id;
    public string $test_string;
    public int $test_int;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTestString(): string
    {
        return $this->test_string;
    }

    /**
     * @return int
     */
    public function getTestInt(): int
    {
        return $this->test_int;
    }
}
