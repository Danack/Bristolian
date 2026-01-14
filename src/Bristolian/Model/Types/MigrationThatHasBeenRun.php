<?php

namespace Bristolian\Model\Types;

class MigrationThatHasBeenRun //implements DataType
{

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
