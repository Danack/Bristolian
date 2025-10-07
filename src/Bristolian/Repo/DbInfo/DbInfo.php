<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\Model\MigrationThatHasBeenRun;
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
    public function getMigrations(): array;
}
