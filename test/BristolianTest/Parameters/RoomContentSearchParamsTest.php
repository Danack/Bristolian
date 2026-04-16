<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\ProcessRule\OptionalStringToRoomSearchTagIds;
use Bristolian\Parameters\RoomContentSearchParams;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use DataType\Value\Ordering;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class RoomContentSearchParamsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::__construct
     * @covers \Bristolian\Parameters\RoomContentSearchParams::default
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getTagIds
     */
    public function test_default_returns_instance_with_empty_tag_ids_and_null_optional_fields(): void
    {
        $params = RoomContentSearchParams::default();
        $this->assertSame([], $params->tag_ids);
        $this->assertSame([], $params->getTagIds());
        $this->assertNull($params->limit);
        $this->assertNull($params->title);
        $this->assertNull($params->description);
        $this->assertNull($params->created_at_after);
        $this->assertNull($params->created_at_before);
        $this->assertNull($params->document_timestamp_after);
        $this->assertNull($params->document_timestamp_before);
        $this->assertNull($params->list_ordering);
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getLimit
     */
    public function test_getLimit_returns_default_when_limit_missing(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([]));
        $this->assertSame(RoomContentSearchParams::DEFAULT_LIMIT, $params->getLimit());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getLimit
     */
    public function test_getLimit_parses_and_clamps_valid_limit(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['limit' => '50']));
        $this->assertSame(50, $params->getLimit());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getLimit
     */
    public function test_getLimit_clamps_to_max_1000(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['limit' => '2000']));
        $this->assertSame(1000, $params->getLimit());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getLimit
     */
    public function test_getLimit_uses_default_when_zero_or_negative(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['limit' => '0']));
        $this->assertSame(RoomContentSearchParams::DEFAULT_LIMIT, $params->getLimit());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getLimit
     */
    public function test_getLimit_uses_default_when_limit_is_empty_string(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['limit' => '']));
        $this->assertSame(RoomContentSearchParams::DEFAULT_LIMIT, $params->getLimit());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getTagIds
     */
    public function test_createFromVarMap_parses_tag_ids_comma_separated_trimmed(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['tag_ids' => ' id1 , id2 , id3 '])
        );
        $this->assertSame(['id1', 'id2', 'id3'], $params->tag_ids);
        $this->assertSame(['id1', 'id2', 'id3'], $params->getTagIds());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getTagIds
     */
    public function test_createFromVarMap_tag_ids_empty_when_missing(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([]));
        $this->assertSame([], $params->getTagIds());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::__construct
     */
    public function test_createFromVarMap_rejects_empty_tag_in_list(): void
    {
        $this->expectValidationException(
            ['tag_ids' => 'a,,b'],
            ['/tag_ids' => OptionalStringToRoomSearchTagIds::ERROR_EMPTY_TAG]
        );
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::__construct
     */
    public function test_createFromVarMap_rejects_more_than_five_tags(): void
    {
        $this->expectValidationException(
            ['tag_ids' => 'a,b,c,d,e,f'],
            ['/tag_ids' => OptionalStringToRoomSearchTagIds::ERROR_TOO_MANY_TAGS]
        );
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getCreatedAtAfterForSql
     */
    public function test_getCreatedAtAfterForSql_returns_formatted_string_or_null(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([]));
        $this->assertNull($params->getCreatedAtAfterForSql());

        $params = RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['created_at_after' => '2024-01-15 10:30:00'])
        );
        $this->assertSame('2024-01-15 10:30:00', $params->getCreatedAtAfterForSql());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getCreatedAtBeforeForSql
     */
    public function test_getCreatedAtBeforeForSql_returns_formatted_string_or_null(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['created_at_before' => '2024-06-01 00:00:00'])
        );
        $this->assertSame('2024-06-01 00:00:00', $params->getCreatedAtBeforeForSql());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getDocumentTimestampAfterForSql
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getDocumentTimestampBeforeForSql
     */
    public function test_getDocumentTimestampAfterForSql_and_getDocumentTimestampBeforeForSql(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([]));
        $this->assertNull($params->getDocumentTimestampAfterForSql());
        $this->assertNull($params->getDocumentTimestampBeforeForSql());

        $params = RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap([
                'document_timestamp_after' => '2024-02-01 00:00:00',
                'document_timestamp_before' => '2024-02-28 23:59:59',
            ])
        );
        $this->assertSame('2024-02-01 00:00:00', $params->getDocumentTimestampAfterForSql());
        $this->assertSame('2024-02-28 23:59:59', $params->getDocumentTimestampBeforeForSql());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::__construct
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getLimit
     * @covers \Bristolian\Parameters\RoomContentSearchParams::getTagIds
     */
    public function test_createFromVarMap_accepts_all_optional_fields(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap([
                'limit' => '10',
                'title' => 'foo',
                'description' => 'bar',
                'created_at_after' => '2024-01-01 00:00:00',
                'created_at_before' => '2024-12-31 23:59:59',
                'document_timestamp_after' => '2024-03-01 00:00:00',
                'document_timestamp_before' => '2024-03-31 23:59:59',
                'tag_ids' => 't1,t2',
                'order' => '+name',
            ])
        );
        $this->assertSame(10, $params->getLimit());
        $this->assertSame('foo', $params->title);
        $this->assertSame('bar', $params->description);
        $this->assertNotNull($params->created_at_after);
        $this->assertSame('2024-01-01 00:00:00', $params->created_at_after->format('Y-m-d H:i:s'));
        $this->assertNotNull($params->created_at_before);
        $this->assertNotNull($params->document_timestamp_after);
        $this->assertNotNull($params->document_timestamp_before);
        $this->assertSame(['t1', 't2'], $params->getTagIds());
        $this->assertInstanceOf(Ordering::class, $params->list_ordering);
        $this->assertSame(['name' => Ordering::ASC], $params->list_ordering->toOrderArray());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::__construct
     */
    public function test_createFromVarMap_order_missing_means_null_list_ordering(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([]));
        $this->assertNull($params->list_ordering);
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::__construct
     */
    public function test_createFromVarMap_order_parses_minus_prefix_desc(): void
    {
        $params = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['order' => '-size']));
        $this->assertNotNull($params->list_ordering);
        $this->assertSame(['size' => Ordering::DESC], $params->list_ordering->toOrderArray());
    }

    /**
     * @covers \Bristolian\Parameters\RoomContentSearchParams::__construct
     */
    public function test_createFromVarMap_order_rejects_unknown_segment(): void
    {
        $this->expectValidationException(
            ['order' => '+not_a_column'],
            ['/order' => Messages::ORDER_VALUE_UNKNOWN]
        );
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, string> $expectedProblems
     */
    private function expectValidationException(array $input, array $expectedProblems): void
    {
        try {
            RoomContentSearchParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems($ve->getValidationProblems(), $expectedProblems);
        }
    }
}
