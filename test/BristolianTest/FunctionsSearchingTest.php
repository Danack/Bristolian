<?php

declare(strict_types=1);

namespace BristolianTest;

use Bristolian\Model\Types\RoomFileInRoom;
use DataType\Value\OrderElement;
use DataType\Value\Ordering;
use PHPUnit\Framework\Attributes\DataProvider;
use function Bristolian\Repo\RoomFileRepo\compare_room_file_document_timestamp;
use function Bristolian\Repo\RoomFileRepo\compare_room_files_for_list_sort;
use function Bristolian\Repo\RoomFileRepo\room_files_sql_order_by_clause;

/**
 * @coversNothing
 */
class FunctionsSearchingTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{?Ordering, string}>
     */
    public static function provides_room_files_sql_order_by_clause_and_expected_sql(): \Generator
    {
        yield 'null ordering uses newest first default' => [null, 'sf.created_at desc'];
        yield 'empty ordering uses newest first default' => [new Ordering([]), 'sf.created_at desc'];
        yield 'name ascending' => [new Ordering([new OrderElement('name', Ordering::ASC)]), 'sf.original_filename ASC, sf.id asc'];
        yield 'size descending' => [new Ordering([new OrderElement('size', Ordering::DESC)]), 'sf.size DESC, sf.id asc'];
        yield 'added ascending' => [new Ordering([new OrderElement('added', Ordering::ASC)]), 'sf.created_at ASC, sf.id asc'];
        yield 'document date descending' => [
            new Ordering([new OrderElement('document_date', Ordering::DESC)]),
            '(rf.document_timestamp is null) asc, rf.document_timestamp DESC, sf.id asc'
        ];
        yield 'unknown ordering falls back to default' => [new Ordering([new OrderElement('unknown', Ordering::ASC)]), 'sf.created_at desc'];
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\room_files_sql_order_by_clause
     * @dataProvider provides_room_files_sql_order_by_clause_and_expected_sql
     */
    public function test_room_files_sql_order_by_clause_returns_expected_sql(
        ?Ordering $list_ordering,
        string $expectedSql
    ): void {
        $this->assertSame($expectedSql, room_files_sql_order_by_clause($list_ordering));
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\compare_room_files_for_list_sort
     */
    public function test_compare_room_files_for_list_sort_uses_newest_first_when_ordering_is_null_or_empty(): void
    {
        $older = $this->createRoomFileInRoom('older', 'b.pdf', 100, '2024-01-01 00:00:00');
        $newer = $this->createRoomFileInRoom('newer', 'a.pdf', 100, '2024-01-02 00:00:00');

        $this->assertSame(1, compare_room_files_for_list_sort($older, $newer, null));
        $this->assertSame(1, compare_room_files_for_list_sort($older, $newer, new Ordering([])));
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\compare_room_files_for_list_sort
     */
    public function test_compare_room_files_for_list_sort_supports_name_size_and_added_ordering(): void
    {
        $alpha = $this->createRoomFileInRoom('file_a', 'alpha.pdf', 10, '2024-01-01 00:00:00');
        $beta = $this->createRoomFileInRoom('file_b', 'beta.pdf', 99, '2024-01-02 00:00:00');

        $this->assertLessThan(
            0,
            compare_room_files_for_list_sort(
                $alpha,
                $beta,
                new Ordering([new OrderElement('name', Ordering::ASC)])
            )
        );

        $this->assertGreaterThan(
            0,
            compare_room_files_for_list_sort(
                $alpha,
                $beta,
                new Ordering([new OrderElement('size', Ordering::DESC)])
            )
        );

        $this->assertLessThan(
            0,
            compare_room_files_for_list_sort(
                $alpha,
                $beta,
                new Ordering([new OrderElement('added', Ordering::ASC)])
            )
        );
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\compare_room_files_for_list_sort
     * @covers \Bristolian\Repo\RoomFileRepo\compare_room_file_document_timestamp
     */
    public function test_compare_room_files_for_list_sort_handles_document_date_sorting_and_stable_id_tiebreak(): void
    {
        $withoutDate = $this->createRoomFileInRoom('file_z', 'z.pdf', 10, '2024-01-01 00:00:00');
        $withDate = $this->createRoomFileInRoom(
            'file_a',
            'a.pdf',
            10,
            '2024-01-01 00:00:00',
            '2024-03-01 00:00:00'
        );

        $documentDateAscending = new Ordering([new OrderElement('document_date', Ordering::ASC)]);
        $this->assertGreaterThan(0, compare_room_files_for_list_sort($withoutDate, $withDate, $documentDateAscending));

        $sameNameFirst = $this->createRoomFileInRoom('a_id', 'same.pdf', 10, '2024-01-01 00:00:00');
        $sameNameSecond = $this->createRoomFileInRoom('b_id', 'same.pdf', 10, '2024-01-01 00:00:00');
        $nameAscending = new Ordering([new OrderElement('name', Ordering::ASC)]);
        $this->assertLessThan(0, compare_room_files_for_list_sort($sameNameFirst, $sameNameSecond, $nameAscending));

        $unknownOrdering = new Ordering([new OrderElement('unknown', Ordering::ASC)]);
        $newer = $this->createRoomFileInRoom('newer', 'same.pdf', 10, '2024-01-02 00:00:00');
        $older = $this->createRoomFileInRoom('older', 'same.pdf', 10, '2024-01-01 00:00:00');
        $this->assertGreaterThan(0, compare_room_files_for_list_sort($older, $newer, $unknownOrdering));
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\compare_room_file_document_timestamp
     */
    public function test_compare_room_file_document_timestamp_sorts_nulls_last_and_compares_real_dates(): void
    {
        $withoutDateOne = $this->createRoomFileInRoom('file_1', 'a.pdf', 10, '2024-01-01 00:00:00');
        $withoutDateTwo = $this->createRoomFileInRoom('file_2', 'b.pdf', 10, '2024-01-01 00:00:00');
        $withEarlierDate = $this->createRoomFileInRoom(
            'file_3',
            'c.pdf',
            10,
            '2024-01-01 00:00:00',
            '2024-01-10 00:00:00'
        );
        $withLaterDate = $this->createRoomFileInRoom(
            'file_4',
            'd.pdf',
            10,
            '2024-01-01 00:00:00',
            '2024-02-10 00:00:00'
        );

        $this->assertSame(0, compare_room_file_document_timestamp($withoutDateOne, $withoutDateTwo));
        $this->assertSame(1, compare_room_file_document_timestamp($withoutDateOne, $withEarlierDate));
        $this->assertSame(-1, compare_room_file_document_timestamp($withEarlierDate, $withoutDateOne));
        $this->assertLessThan(0, compare_room_file_document_timestamp($withEarlierDate, $withLaterDate));
    }

    private function createRoomFileInRoom(
        string $id,
        string $originalFilename,
        int $size,
        string $createdAt,
        ?string $documentTimestamp = null
    ): RoomFileInRoom {
        return new RoomFileInRoom(
            id: $id,
            normalized_name: $originalFilename,
            original_filename: $originalFilename,
            state: 'uploaded',
            size: $size,
            user_id: 'user_1',
            created_at: new \DateTimeImmutable($createdAt),
            document_timestamp: $documentTimestamp !== null ? new \DateTimeImmutable($documentTimestamp) : null,
            description: null,
            note: null
        );
    }
}
