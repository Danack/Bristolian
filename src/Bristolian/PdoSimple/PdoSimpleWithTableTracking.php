<?php

declare(strict_types=1);

namespace Bristolian\PdoSimple;

use Bristolian\Cache\TableAccessRecorder;
use Bristolian\Cache\UnknownQueryHandler;

class PdoSimpleWithTableTracking extends PdoSimple
{
    /** @var array<string, array{read: string[], write: string[]}> */
    private array $exactMappings;

    /** @var array<array{pattern: string, read: string[], write: string[]}> */
    private array $patternMappings;

    private TableAccessRecorder $recorder;

    private UnknownQueryHandler $unknownQueryHandler;

    /**
     * @param \PDO $pdo
     * @param TableAccessRecorder $recorder
     * @param array<string, array{read: string[], write: string[]}> $exactMappings
     * @param array<array{pattern: string, read: string[], write: string[]}> $patternMappings
     * @param UnknownQueryHandler $unknownQueryHandler
     */
    public function __construct(
        \PDO $pdo,
        TableAccessRecorder $recorder,
        array $exactMappings,
        array $patternMappings,
        UnknownQueryHandler $unknownQueryHandler
    ) {
        parent::__construct($pdo);
        $this->recorder = $recorder;
        $this->patternMappings = $patternMappings;
        $this->unknownQueryHandler = $unknownQueryHandler;

        $normalized = [];
        foreach ($exactMappings as $query => $tags) {
            $normalized[self::normalizeWhitespace($query)] = $tags;
        }
        $this->exactMappings = $normalized;
    }

    private static function normalizeWhitespace(string $sql): string
    {
        return preg_replace('/\s+/', ' ', trim($sql));
    }

    /**
     * @param string $query
     * @return array{read: string[], write: string[]}|null
     */
    private function lookupTags(string $query): array|null
    {
        $normalized = self::normalizeWhitespace($query);

        if (isset($this->exactMappings[$normalized])) {
            return $this->exactMappings[$normalized];
        }

        foreach ($this->patternMappings as $entry) {
            if (preg_match($entry['pattern'], $normalized) === 1) {
                return ['read' => $entry['read'], 'write' => $entry['write']];
            }
        }

        return null;
    }

    private function recordForQuery(string $query, bool $isWrite): void
    {
        $tags = $this->lookupTags($query);

        if ($tags === null) {
            $this->unknownQueryHandler->handle($query);
            // Hmm.
            return;
        }

        if (count($tags['read']) > 0) {
            $this->recorder->recordTablesRead($tags['read']);
        }
        if (count($tags['write']) > 0) {
            $this->recorder->recordTablesWritten($tags['write']);
        }
    }

    public function execute(string $query, array $params): int
    {
        $this->recordForQuery($query, true);
        return parent::execute($query, $params);
    }

    public function insert(string $query, array $params): int
    {
        $this->recordForQuery($query, true);
        return parent::insert($query, $params);
    }

    public function fetchOneAsObject(string $query, array $params, string $classname)
    {
        $this->recordForQuery($query, false);
        return parent::fetchOneAsObject($query, $params, $classname);
    }

    public function fetchOneAsObjectConstructor(string $query, array $params, string $classname)
    {
        $this->recordForQuery($query, false);
        return parent::fetchOneAsObjectConstructor($query, $params, $classname);
    }

    public function fetchOneAsObjectOrNull(string $query, array $params, string $classname)
    {
        $this->recordForQuery($query, false);
        return parent::fetchOneAsObjectOrNull($query, $params, $classname);
    }

    public function fetchOneAsObjectOrNullConstructor(string $query, array $params, string $classname)
    {
        $this->recordForQuery($query, false);
        return parent::fetchOneAsObjectOrNullConstructor($query, $params, $classname);
    }

    public function fetchAllAsObjectConstructor(string $query, array $params, string $classname): array
    {
        $this->recordForQuery($query, false);
        return parent::fetchAllAsObjectConstructor($query, $params, $classname);
    }

    public function fetchOneAsDataOrNull(string $query, array $params): array|null
    {
        $this->recordForQuery($query, false);
        return parent::fetchOneAsDataOrNull($query, $params);
    }

    public function fetchAllAsData(string $query, array $params): array
    {
        $this->recordForQuery($query, false);
        return parent::fetchAllAsData($query, $params);
    }

    public function fetchAllAsObject(string $query, array $params, string $classname)
    {
        $this->recordForQuery($query, false);
        return parent::fetchAllAsObject($query, $params, $classname);
    }

    public function fetchAllRowsAsScalar(string $query, array $params): array
    {
        $this->recordForQuery($query, false);
        return parent::fetchAllRowsAsScalar($query, $params);
    }
}
