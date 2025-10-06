<?php

namespace Bristolian\Model;

//use Bristolian\Parameters\PropertyType\BasicDateTime;
//use Bristolian\Parameters\PropertyType\BasicString;
//use Bristolian\Parameters\PropertyType\SourceLinkPositionValue;
//use DataType\Create\CreateArrayOfTypeFromArray;
//use DataType\Create\CreateFromArray;
//use DataType\DataType;
//use DataType\GetInputTypesFromAttributes;

class MigrationFromCode
{
    public function __construct(
        public readonly int $id,
        public readonly string $description,
        /**
         * @var string[]
         */
        public readonly array $queries_to_run,
    ) {
    }
}
