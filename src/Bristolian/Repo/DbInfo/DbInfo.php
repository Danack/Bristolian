<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Database\migrations;
use Bristolian\Model\Types\MigrationThatHasBeenRun;
use Bristolian\Parameters\Table;

/**
 * Interface for getting simple DB schema info.
 */
interface DbInfo
{
    /**
     * @return Table[]
     */
    public function getTableInfo(): array;


    /**
     * @return MigrationThatHasBeenRun[]
     */
    #[ReadsTable(migrations::class)]
    public function getMigrations(): array;
}
