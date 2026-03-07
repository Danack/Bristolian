<?php

declare(strict_types=1);

namespace Bristolian\Cache;

class ThrowOnUnknownQuery implements UnknownQueryHandler
{
    public function handle(string $query): void
    {
        $message = "Unknown query not in cache tag mapping.\n"
            . "\n"
            . "Query: " . substr($query, 0, 200) . "\n"
            . "\n"
            . "To fix this, add an entry for this query in\n"
            . "src/Bristolian/Cache/QueryTagMapping.php :: getExactMappings().\n"
            . "\n"
            . "Each entry maps a SQL string to the tables it reads from and writes to:\n"
            . "  trim(\$sql) => ['read' => ['table_name'], 'write' => []],\n"
            . "\n"
            . "Use the generated Database constant classes (e.g. Bristolian\\Database\\table_name::SELECT)\n"
            . "where possible so the mapping stays in sync with table helpers.\n"
            . "Whitespace differences are normalised at lookup time, so exact indentation does not matter.\n"
            . "For dynamic queries with variable IN clauses, add a regex pattern in getPatternMappings() instead.";

        throw new \RuntimeException($message);
    }
}
