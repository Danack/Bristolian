<?php

declare(strict_types = 1);

namespace Bristolian\Parameters;

/**
 * Request body for setting tags on a room file, link, or annotation.
 * tag_ids must be ids of tags that belong to the room (validated in controller).
 */
class SetEntityTagsParam
{
    /**
     * @param string[] $tag_ids
     */
    public function __construct(
        public readonly array $tag_ids
    ) {
    }

    /**
     * @param array<string, mixed> $data from JSON body (e.g. ['tag_ids' => ['id1', 'id2']])
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $raw = $data['tag_ids'] ?? [];
        if (!is_array($raw)) {
            $raw = [];
        }
        $tag_ids = [];
        foreach ($raw as $v) {
            if (is_string($v)) {
                $tag_ids[] = $v;
            }
        }
        return new self($tag_ids);
    }
}
