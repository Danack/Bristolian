<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\DataType\Table;
use Bristolian\DataType\Migration;

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
