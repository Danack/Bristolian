<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

class DatasourcesEntryTypeFinder implements EntryTypeFinder
{
    public function getEntryTypeKey(): string
    {
        return 'datasources';
    }

    public function findEntries(): array
    {
        $projectRoot = dirname(__DIR__, 4);

        return RepoInterfaceImplementationDiscovery::findDatasourceEntries($projectRoot);
    }
}
