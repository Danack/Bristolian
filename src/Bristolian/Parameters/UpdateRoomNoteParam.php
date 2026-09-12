<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use DataType\Basic\OptionalBasicString;
use Bristolian\Parameters\PropertyType\RoomNoteMarkdown;
use Bristolian\Parameters\PropertyType\RoomNoteTitle;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class UpdateRoomNoteParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[RoomNoteTitle('title')]
        public readonly string $title,
        #[RoomNoteMarkdown('markdown')]
        public readonly string $markdown,
        #[OptionalBasicString('document_timestamp')]
        public readonly string|null $document_timestamp,
    ) {
    }
}
