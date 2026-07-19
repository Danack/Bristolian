<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan;

use Bristolian\PHPStan\SqlTableReferenceExtractor;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\PHPStan\SqlTableReferenceExtractor
 */
class SqlTableReferenceExtractorTest extends BaseTestCase
{
    private SqlTableReferenceExtractor $extractor;

    public function setUp(): void
    {
        parent::setUp();
        $this->extractor = new SqlTableReferenceExtractor();
    }

    public function test_extract_select_with_join_reads_both_tables(): void
    {
        $sql = <<< SQL
select
    mt.id,
    mt.user_id,
    mt.meme_id,
    mt.type,
    mt.text,
    mt.created_at
from
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
where
  sm.user_id = :user_id and
  mt.meme_id = :meme_id
SQL;

        $result = $this->extractor->extract($sql);

        $this->assertSame(['meme_tag', 'stored_meme'], $result['reads']);
        $this->assertSame([], $result['writes']);
    }

    public function test_extract_update_with_join_writes_primary_and_reads_join(): void
    {
        $sql = <<< SQL
update
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
set
  mt.type = :user_tag_type,
  mt.text = :text
where
  sm.user_id = :user_id and
  mt.id = :meme_tag_id
SQL;

        $result = $this->extractor->extract($sql);

        $this->assertSame(['stored_meme'], $result['reads']);
        $this->assertSame(['meme_tag'], $result['writes']);
    }

    public function test_extract_delete_with_join_writes_primary_and_reads_join(): void
    {
        $sql = <<< SQL
delete mt from
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
where
  sm.user_id = :user_id and
  mt.id = :meme_tag_id
SQL;

        $result = $this->extractor->extract($sql);

        $this->assertSame(['stored_meme'], $result['reads']);
        $this->assertSame(['meme_tag'], $result['writes']);
    }

    public function test_extract_insert_into_is_write(): void
    {
        $sql = <<< SQL
insert into meme_tag (
    id,
    user_id,
    meme_id,
    type,
    text
)
values (
    :id,
    :user_id,
    :meme_id,
    :type,
    :text
)
SQL;

        $result = $this->extractor->extract($sql);

        $this->assertSame([], $result['reads']);
        $this->assertSame(['meme_tag'], $result['writes']);
    }
}
