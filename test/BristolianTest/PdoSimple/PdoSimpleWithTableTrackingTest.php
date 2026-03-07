<?php

declare(strict_types=1);

namespace BristolianTest\PdoSimple;

use Bristolian\Cache\TestTableAccessRecorder;
use Bristolian\Cache\ThrowOnUnknownQuery;
use Bristolian\Cache\UnknownQueryHandler;
use Bristolian\Database\pdo_simple_test;
use Bristolian\PdoSimple\PdoSimpleWithTableTracking;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\PdoSimple\PdoSimpleWithTableTracking
 * @group db
 */
class PdoSimpleWithTableTrackingTest extends BaseTestCase
{
    private TestTableAccessRecorder $recorder;

    /** @var array<string, array{read: string[], write: string[]}> */
    private array $exactMappings;

    /** @var array<array{pattern: string, read: string[], write: string[]}> */
    private array $patternMappings;

    private UnknownQueryHandler $unknownQueryHandler;

    public function setup(): void
    {
        parent::setup();
        $this->recorder = new TestTableAccessRecorder();
        $this->unknownQueryHandler = new ThrowOnUnknownQuery();
        $this->exactMappings = [];
        $this->patternMappings = [];
    }

    private function createTracker(): PdoSimpleWithTableTracking
    {
        $pdo = $this->injector->make(\PDO::class);
        return new PdoSimpleWithTableTracking(
            $pdo,
            $this->recorder,
            $this->exactMappings,
            $this->patternMappings,
            $this->unknownQueryHandler
        );
    }

    public function testExactMappingRecordsRead(): void
    {
        $query = pdo_simple_test::SELECT;
        $this->exactMappings[trim($query)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllAsData($query, []);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
        $this->assertSame(['pdo_simple_test'], $reads[0]);

        $writes = $this->recorder->getRecordedWrites();
        $this->assertCount(0, $writes);
    }

    public function testExactMappingRecordsWrite(): void
    {
        $insertQuery = pdo_simple_test::INSERT;
        $this->exactMappings[trim($insertQuery)] = [
            'read' => [],
            'write' => ['pdo_simple_test'],
        ];

        $tracker = $this->createTracker();
        $tracker->insert($insertQuery, [
            'test_string' => 'tracking_test_' . time(),
            'test_int' => 42,
        ]);

        $writes = $this->recorder->getRecordedWrites();
        $this->assertCount(1, $writes);
        $this->assertSame(['pdo_simple_test'], $writes[0]);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(0, $reads);
    }

    public function testExactMappingRecordsBothReadAndWrite(): void
    {
        $insertQuery = pdo_simple_test::INSERT;
        $this->exactMappings[trim($insertQuery)] = [
            'read' => ['other_table'],
            'write' => ['pdo_simple_test'],
        ];

        $tracker = $this->createTracker();
        $tracker->insert($insertQuery, [
            'test_string' => 'tracking_test_both_' . time(),
            'test_int' => 99,
        ]);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
        $this->assertSame(['other_table'], $reads[0]);

        $writes = $this->recorder->getRecordedWrites();
        $this->assertCount(1, $writes);
        $this->assertSame(['pdo_simple_test'], $writes[0]);
    }

    public function testPatternMappingMatchesQuery(): void
    {
        $this->patternMappings[] = [
            'pattern' => '/^select\b.*\bfrom\s+pdo_simple_test\b/si',
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $query = pdo_simple_test::SELECT . " where id = :id limit 1";
        $tracker = $this->createTracker();
        $tracker->fetchOneAsDataOrNull($query, [':id' => 1]);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
        $this->assertSame(['pdo_simple_test'], $reads[0]);
    }

    public function testExactMatchTakesPriorityOverPattern(): void
    {
        $query = pdo_simple_test::SELECT;
        $this->exactMappings[trim($query)] = [
            'read' => ['exact_match_table'],
            'write' => [],
        ];
        $this->patternMappings[] = [
            'pattern' => '/^select\b.*\bfrom\s+pdo_simple_test\b/si',
            'read' => ['pattern_match_table'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllAsData($query, []);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
        $this->assertSame(['exact_match_table'], $reads[0]);
    }

    public function testUnknownQueryTriggersHandler(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown query not in cache tag mapping');

        $tracker->fetchAllAsData(pdo_simple_test::SELECT, []);
    }

    public function testNonThrowingHandlerReturnsEarlyWithoutRecording(): void
    {
        $handledQueries = [];
        $this->unknownQueryHandler = new class ($handledQueries) implements UnknownQueryHandler {
            /** @var string[] */
            public array $queries;
            /**
             * @param string[] $queries
             */
            public function __construct(array &$queries)
            {
                $this->queries = &$queries;
            }
            public function handle(string $query): void
            {
                $this->queries[] = $query;
            }
        };

        $tracker = $this->createTracker();
        $tracker->fetchAllAsData(pdo_simple_test::SELECT, []);

        $this->assertCount(1, $handledQueries);
        $this->assertCount(0, $this->recorder->getRecordedReads());
        $this->assertCount(0, $this->recorder->getRecordedWrites());
    }

    public function testLeadingTrailingWhitespaceIsNormalized(): void
    {
        $query = pdo_simple_test::SELECT;
        $this->exactMappings[trim($query)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $paddedQuery = "  \n" . $query . "  \n";
        $tracker = $this->createTracker();
        $tracker->fetchAllAsData($paddedQuery, []);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
        $this->assertSame(['pdo_simple_test'], $reads[0]);
    }

    public function testInternalWhitespaceIsNormalized(): void
    {
        $this->exactMappings['select id from pdo_simple_test'] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $queryWithDifferentIndentation = "select\n  id\nfrom\n  pdo_simple_test";
        $tracker = $this->createTracker();
        $tracker->fetchAllRowsAsScalar($queryWithDifferentIndentation, []);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
        $this->assertSame(['pdo_simple_test'], $reads[0]);
    }

    public function testExecuteRecordsTracking(): void
    {
        $updateQuery = pdo_simple_test::UPDATE;
        $this->exactMappings[trim($updateQuery)] = [
            'read' => [],
            'write' => ['pdo_simple_test'],
        ];

        $insertQuery = pdo_simple_test::INSERT;
        $this->exactMappings[trim($insertQuery)] = [
            'read' => [],
            'write' => ['pdo_simple_test'],
        ];

        $tracker = $this->createTracker();

        $insertId = $tracker->insert($insertQuery, [
            'test_string' => 'execute_test_' . time(),
            'test_int' => 1,
        ]);

        $tracker->execute($updateQuery, [
            'id' => $insertId,
            'test_string' => 'execute_test_updated_' . time(),
            'test_int' => 2,
        ]);

        $writes = $this->recorder->getRecordedWrites();
        $this->assertCount(2, $writes);
    }

    public function testFetchAllAsObjectRecordsTracking(): void
    {
        $query = pdo_simple_test::SELECT;
        $this->exactMappings[trim($query)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllAsObject($query, [], PdoSimpleTestObject::class);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
    }

    public function testFetchAllAsObjectConstructorRecordsTracking(): void
    {
        $query = pdo_simple_test::SELECT;
        $this->exactMappings[trim($query)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllAsObjectConstructor($query, [], PdoSimpleTestObjectConstructor::class);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
    }

    public function testFetchAllRowsAsScalarRecordsTracking(): void
    {
        $query = "select test_string from pdo_simple_test";
        $this->exactMappings[$query] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllRowsAsScalar($query, []);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
    }

    public function testFetchOneAsObjectRecordsTracking(): void
    {
        $insertQuery = pdo_simple_test::INSERT;
        $selectQuery = pdo_simple_test::SELECT . " where id = :id";

        $this->exactMappings[trim($insertQuery)] = [
            'read' => [],
            'write' => ['pdo_simple_test'],
        ];
        $this->exactMappings[trim($selectQuery)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $insertId = $tracker->insert($insertQuery, [
            'test_string' => 'fetch_one_test_' . time(),
            'test_int' => 7,
        ]);

        $tracker->fetchOneAsObject(
            $selectQuery,
            [':id' => $insertId],
            PdoSimpleTestObjectConstructor::class
        );

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
        $this->assertSame(['pdo_simple_test'], $reads[0]);
    }

    public function testFetchOneAsObjectConstructorRecordsTracking(): void
    {
        $insertQuery = pdo_simple_test::INSERT;
        $selectQuery = pdo_simple_test::SELECT . " where id = :id";

        $this->exactMappings[trim($insertQuery)] = [
            'read' => [],
            'write' => ['pdo_simple_test'],
        ];
        $this->exactMappings[trim($selectQuery)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $insertId = $tracker->insert($insertQuery, [
            'test_string' => 'fetch_one_ctor_test_' . time(),
            'test_int' => 8,
        ]);

        $tracker->fetchOneAsObjectConstructor(
            $selectQuery,
            [':id' => $insertId],
            PdoSimpleTestObjectConstructor::class
        );

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
    }

    public function testFetchOneAsObjectOrNullRecordsTracking(): void
    {
        $query = pdo_simple_test::SELECT . " where id = :id";
        $this->exactMappings[trim($query)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $result = $tracker->fetchOneAsObjectOrNull(
            $query,
            [':id' => -1],
            PdoSimpleTestObjectConstructor::class
        );

        $this->assertNull($result);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
    }

    public function testFetchOneAsObjectOrNullConstructorRecordsTracking(): void
    {
        $query = pdo_simple_test::SELECT . " where id = :id";
        $this->exactMappings[trim($query)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $result = $tracker->fetchOneAsObjectOrNullConstructor(
            $query,
            [':id' => -1],
            PdoSimpleTestObjectConstructor::class
        );

        $this->assertNull($result);

        $reads = $this->recorder->getRecordedReads();
        $this->assertCount(1, $reads);
    }

    public function testGetTagsForResponseFormatsCorrectly(): void
    {
        $query = pdo_simple_test::SELECT;
        $this->exactMappings[trim($query)] = [
            'read' => ['pdo_simple_test', 'other_table'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllAsData($query, []);

        $tags = $this->recorder->getTagsForResponse();
        $this->assertStringContainsString('table:pdo_simple_test', $tags);
        $this->assertStringContainsString('table:other_table', $tags);
    }

    public function testGetTablesWrittenAfterInsert(): void
    {
        $insertQuery = pdo_simple_test::INSERT;
        $this->exactMappings[trim($insertQuery)] = [
            'read' => [],
            'write' => ['pdo_simple_test'],
        ];

        $tracker = $this->createTracker();
        $tracker->insert($insertQuery, [
            'test_string' => 'written_tables_test_' . time(),
            'test_int' => 55,
        ]);

        $tablesWritten = $this->recorder->getTablesWritten();
        $this->assertContains('pdo_simple_test', $tablesWritten);
    }

    public function testClearResetsRecorder(): void
    {
        $query = pdo_simple_test::SELECT;
        $this->exactMappings[trim($query)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllAsData($query, []);

        $this->assertCount(1, $this->recorder->getRecordedReads());

        $this->recorder->clear();

        $this->assertCount(0, $this->recorder->getRecordedReads());
        $this->assertCount(0, $this->recorder->getRecordedWrites());
        $this->assertSame('', $this->recorder->getTagsForResponse());
        $this->assertSame([], $this->recorder->getTablesWritten());
    }

    public function testMultipleQueriesAccumulateTags(): void
    {
        $selectQuery = pdo_simple_test::SELECT;
        $insertQuery = pdo_simple_test::INSERT;

        $this->exactMappings[trim($selectQuery)] = [
            'read' => ['pdo_simple_test'],
            'write' => [],
        ];
        $this->exactMappings[trim($insertQuery)] = [
            'read' => [],
            'write' => ['pdo_simple_test'],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllAsData($selectQuery, []);
        $tracker->insert($insertQuery, [
            'test_string' => 'multi_query_test_' . time(),
            'test_int' => 66,
        ]);

        $reads = $this->recorder->getRecordedReads();
        $writes = $this->recorder->getRecordedWrites();
        $this->assertCount(1, $reads);
        $this->assertCount(1, $writes);
    }

    public function testPatternMappingNotMatchedFallsToUnknown(): void
    {
        $this->patternMappings[] = [
            'pattern' => '/^select\b.*\bfrom\s+nonexistent_table\b/si',
            'read' => ['nonexistent_table'],
            'write' => [],
        ];

        $tracker = $this->createTracker();

        $this->expectException(\RuntimeException::class);
        $tracker->fetchAllAsData(pdo_simple_test::SELECT, []);
    }

    public function testEmptyReadAndWriteArraysDoNotRecord(): void
    {
        $query = pdo_simple_test::SELECT;
        $this->exactMappings[trim($query)] = [
            'read' => [],
            'write' => [],
        ];

        $tracker = $this->createTracker();
        $tracker->fetchAllAsData($query, []);

        $this->assertCount(0, $this->recorder->getRecordedReads());
        $this->assertCount(0, $this->recorder->getRecordedWrites());
    }
}
