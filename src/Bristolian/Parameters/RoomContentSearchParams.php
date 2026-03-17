<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\OptionalBasicString;
use Bristolian\Parameters\PropertyType\RoomContentSearchTagIds;
use Bristolian\StaticFactory;
use DataType\Basic\OptionalDateTime;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Search/filter params for room content list endpoints (files, links, videos).
 * All fields optional; used for default list (limit 20, most recent first).
 */
class RoomContentSearchParams implements DataType, StaticFactory
{
    public const DEFAULT_LIMIT = 20;

    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalBasicString('limit')]
        public readonly ?string $limit,
        #[OptionalBasicString('title')]
        public readonly ?string $title,
        #[OptionalDateTime('created_at_after', ['Y-m-d H:i:s', 'Y-m-d'])]
        public readonly ?\DateTimeInterface $created_at_after,
        #[OptionalDateTime('created_at_before', ['Y-m-d H:i:s', 'Y-m-d'])]
        public readonly ?\DateTimeInterface $created_at_before,
        #[OptionalDateTime('document_timestamp_after', ['Y-m-d H:i:s', 'Y-m-d'])]
        public readonly ?\DateTimeInterface $document_timestamp_after,
        #[OptionalDateTime('document_timestamp_before', ['Y-m-d H:i:s', 'Y-m-d'])]
        public readonly ?\DateTimeInterface $document_timestamp_before,
        #[RoomContentSearchTagIds('tag_ids')]
        public readonly array $tag_ids,
    ) {
    }

    /**
     * Default params for "list most recent" (limit 20, no filters).
     */
    public static function default(): self
    {
        return new self(null, null, null, null, null, null, []);
    }

    /** SQL-safe string for created_at_after (Y-m-d H:i:s) or null. */
    public function getCreatedAtAfterForSql(): ?string
    {
        return $this->created_at_after?->format('Y-m-d H:i:s');
    }

    /** SQL-safe string for created_at_before (Y-m-d H:i:s) or null. */
    public function getCreatedAtBeforeForSql(): ?string
    {
        return $this->created_at_before?->format('Y-m-d H:i:s');
    }

    /** SQL-safe string for document_timestamp_after (Y-m-d H:i:s) or null. */
    public function getDocumentTimestampAfterForSql(): ?string
    {
        return $this->document_timestamp_after?->format('Y-m-d H:i:s');
    }

    /** SQL-safe string for document_timestamp_before (Y-m-d H:i:s) or null. */
    public function getDocumentTimestampBeforeForSql(): ?string
    {
        return $this->document_timestamp_before?->format('Y-m-d H:i:s');
    }

    /**
     * Tag IDs to filter by (AND semantics: results must have all of these tags).
     *
     * @return string[]
     */
    public function getTagIds(): array
    {
        return $this->tag_ids;
    }

    /**
     * Effective limit to use (default 20, clamped to positive int).
     */
    public function getLimit(): int
    {
        if ($this->limit === null || $this->limit === '') {
            return self::DEFAULT_LIMIT;
        }
        $value = (int) $this->limit;
        return $value < 1 ? self::DEFAULT_LIMIT : min($value, 1000);
    }
}
