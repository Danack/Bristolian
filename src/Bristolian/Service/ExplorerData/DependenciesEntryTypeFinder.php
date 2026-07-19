<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

class DependenciesEntryTypeFinder implements EntryTypeFinder
{
    public function getEntryTypeKey(): string
    {
        return 'dependencies';
    }

    public function findEntries(): array
    {
        $projectRoot = dirname(__DIR__, 4);

        $entries = array_merge(
            RepoInterfaceImplementationDiscovery::findAnnotatedInterfaceEntries($projectRoot),
            ServiceDependencyDiscovery::findEntries($projectRoot)
        );

        usort(
            $entries,
            static fn (array $left, array $right): int => strcmp($left['name'], $right['name'])
        );

        return $entries;
    }
}
