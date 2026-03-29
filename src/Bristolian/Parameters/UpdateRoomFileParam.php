<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\RoomFileDescription;
use Bristolian\Parameters\PropertyType\RoomFileNote;
use Bristolian\Parameters\PropertyType\OptionalBasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Request body for PATCH room file metadata (description, note, document date).
 *
 * {@see document_timestamp} is an optional string (e.g. ISO/datetime-local); empty or omitted clears the stored date.
 */
class UpdateRoomFileParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[RoomFileDescription('description')]
        public readonly ?string $description,
        #[RoomFileNote('note')]
        public readonly ?string $note,
        #[OptionalBasicString('document_timestamp')]
        public readonly ?string $document_timestamp,
    ) {
    }
}
