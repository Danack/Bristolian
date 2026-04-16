<?php

declare(strict_types=1);

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Model\Types\RoomFileInRoom;
use DataType\Value\Ordering;

/**
 * Convert the first room-files ordering element into the SQL ORDER BY fragment.
 * Falls back to the default "newest added first" ordering when no sort is set.
 */
function room_files_sql_order_by_clause(?Ordering $list_ordering): string
{
    if ($list_ordering === null) {
        return 'sf.created_at desc';
    }

    $elements = $list_ordering->getOrderElements();
    $first = $elements[0] ?? null;
    if ($first === null) {
        return 'sf.created_at desc';
    }

    $column_key = $first->getName();
    $direction = $first->getOrder() === Ordering::DESC ? 'DESC' : 'ASC';

    return match ($column_key) {
        'name' => "sf.original_filename {$direction}, sf.id asc",
        'size' => "sf.size {$direction}, sf.id asc",
        'added' => "sf.created_at {$direction}, sf.id asc",
        'document_date' => "(rf.document_timestamp is null) asc, rf.document_timestamp {$direction}, sf.id asc",
        default => 'sf.created_at desc',
    };
}

/**
 * Compare two room files using the requested list ordering for FakeRoomFileRepo usort().
 * Falls back to the default "newest added first" ordering and then a stable id tiebreak.
 */
function compare_room_files_for_list_sort(
    RoomFileInRoom $left,
    RoomFileInRoom $right,
    ?Ordering $list_ordering
): int {
    if ($list_ordering === null) {
        return $right->created_at <=> $left->created_at;
    }

    $elements = $list_ordering->getOrderElements();
    $first = $elements[0] ?? null;
    if ($first === null) {
        return $right->created_at <=> $left->created_at;
    }

    $column_key = $first->getName();
    $ascending = $first->getOrder() === Ordering::ASC;

    $comparison = match ($column_key) {
        'name' => strcmp($left->original_filename, $right->original_filename),
        'size' => $left->size <=> $right->size,
        'added' => $left->created_at <=> $right->created_at,
        'document_date' => compare_room_file_document_timestamp($left, $right),
        default => $right->created_at <=> $left->created_at,
    };

    if ($comparison !== 0) {
        return $ascending ? $comparison : -$comparison;
    }

    return strcmp($left->id, $right->id);
}

/**
 * Compare document timestamps while always sorting null timestamps after real dates.
 */
function compare_room_file_document_timestamp(RoomFileInRoom $left, RoomFileInRoom $right): int
{
    $left_null = $left->document_timestamp === null;
    $right_null = $right->document_timestamp === null;

    if ($left_null && $right_null) {
        return 0;
    }
    if ($left_null) {
        return 1;
    }
    if ($right_null) {
        return -1;
    }

    return $left->document_timestamp <=> $right->document_timestamp;
}
