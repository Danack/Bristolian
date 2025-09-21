<?php

namespace Bristolian\Parameters;

//use Bristolian\Parameters\PropertyType\BasicDateTime;
//use Bristolian\Parameters\PropertyType\BasicString;
//use Bristolian\Parameters\PropertyType\SourceLinkPositionValue;
//use DataType\Create\CreateArrayOfTypeFromArray;
//use DataType\Create\CreateFromArray;
//use DataType\DataType;
//use DataType\GetInputTypesFromAttributes;

class MigrationThatHasBeenRun //implements DataType
{
//    use CreateFromArray;
//    use CreateArrayOfTypeFromArray;
//    use GetInputTypesFromAttributes;

    public function __construct(
//        #[SourceLinkPositionValue('id')]
        public readonly int $id,
//        #[BasicString('description')]
        public readonly string $description, // "Migration 1"
//        #[BasicString('checksum')]

        public readonly string $json_encoded_queries,// // "f2f0f464ae13c9d7835f16088d8eef8b67ae21f982227605f7883a9836ae861f"
//        #[BasicDateTime('created_at')]
        public readonly \DateTimeInterface $created_at // "2023-05-24 11:29:37"
    ) {
    }
}
