<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\DataType\Table;
use Bristolian\DataType\Migration;

interface DbInfo
{
    /**
     * @return Table[]
     */
    function getTableInfo(): array;



    /**
     * @return Migration[]
     */
    function getMigrations(): array;
}
