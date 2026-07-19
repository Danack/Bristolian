<?php

declare(strict_types=1);

namespace Bristolian\PHPStan;

/**
 * Best-effort extraction of database table names from SQL strings.
 *
 * @phpstan-type TableAccess array{reads: list<string>, writes: list<string>}
 */
class SqlTableReferenceExtractor
{
    private const TABLE_NAME_PATTERN = '[a-zA-Z_][a-zA-Z0-9_]*';

    /**
     * @return TableAccess
     */
    public function extract(string $sql): array
    {
        $normalizedSql = preg_replace('/\s+/', ' ', trim($sql));
        if ($normalizedSql === null || $normalizedSql === '') {
            return ['reads' => [], 'writes' => []];
        }

        $reads = [];
        $writes = [];

        $statementType = $this->detectStatementType($normalizedSql);

        if ($statementType === 'insert') {
            foreach ($this->matchTables($normalizedSql, '/\bINSERT\s+INTO\s+(' . self::TABLE_NAME_PATTERN . ')\b/i') as $tableName) {
                $writes[] = $tableName;
            }
            foreach ($this->extractFromAndJoinTables($normalizedSql) as $tableName) {
                $reads[] = $tableName;
            }
        } elseif ($statementType === 'update') {
            $updateTables = $this->matchTables(
                $normalizedSql,
                '/\bUPDATE\s+(' . self::TABLE_NAME_PATTERN . ')\b/i'
            );
            foreach ($updateTables as $tableName) {
                $writes[] = $tableName;
            }
            foreach ($this->extractJoinTables($normalizedSql) as $tableName) {
                $reads[] = $tableName;
            }
            // UPDATE t JOIN other — join partners are reads; also FROM clauses if present
            foreach ($this->extractFromTables($normalizedSql) as $tableName) {
                if (!in_array($tableName, $writes, true)) {
                    $reads[] = $tableName;
                }
            }
        } elseif ($statementType === 'delete') {
            // delete mt from meme_tag mt  OR  delete from meme_tag
            $deleteFromTables = $this->matchTables(
                $normalizedSql,
                '/\bDELETE\s+(?:' . self::TABLE_NAME_PATTERN . '\s+)?FROM\s+(' . self::TABLE_NAME_PATTERN . ')\b/i'
            );
            foreach ($deleteFromTables as $tableName) {
                $writes[] = $tableName;
            }
            foreach ($this->extractJoinTables($normalizedSql) as $tableName) {
                $reads[] = $tableName;
            }
        } else {
            // SELECT and unknown: treat FROM/JOIN as reads
            foreach ($this->extractFromAndJoinTables($normalizedSql) as $tableName) {
                $reads[] = $tableName;
            }
        }

        return [
            'reads' => $this->normalizeTableList($reads),
            'writes' => $this->normalizeTableList($writes),
        ];
    }

    private function detectStatementType(string $sql): string
    {
        if (preg_match('/^\s*INSERT\b/i', $sql) === 1) {
            return 'insert';
        }
        if (preg_match('/^\s*UPDATE\b/i', $sql) === 1) {
            return 'update';
        }
        if (preg_match('/^\s*DELETE\b/i', $sql) === 1) {
            return 'delete';
        }

        return 'select';
    }

    /**
     * @return list<string>
     */
    private function extractFromAndJoinTables(string $sql): array
    {
        return array_merge(
            $this->extractFromTables($sql),
            $this->extractJoinTables($sql)
        );
    }

    /**
     * @return list<string>
     */
    private function extractFromTables(string $sql): array
    {
        return $this->matchTables(
            $sql,
            '/\bFROM\s+(' . self::TABLE_NAME_PATTERN . ')\b/i'
        );
    }

    /**
     * @return list<string>
     */
    private function extractJoinTables(string $sql): array
    {
        return $this->matchTables(
            $sql,
            '/\b(?:INNER|LEFT|RIGHT|CROSS)?\s*JOIN\s+(' . self::TABLE_NAME_PATTERN . ')\b/i'
        );
    }

    /**
     * @return list<string>
     */
    private function matchTables(string $sql, string $pattern): array
    {
        if (preg_match_all($pattern, $sql, $matches) !== false) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @param list<string> $tableNames
     * @return list<string>
     */
    private function normalizeTableList(array $tableNames): array
    {
        $normalized = [];
        foreach ($tableNames as $tableName) {
            $normalized[] = strtolower($tableName);
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }
}
