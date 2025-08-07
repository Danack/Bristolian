<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\Parameters\Migration;
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
     * @return Migration[]
     */
    public function getMigrations(): array;
}
