<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\DataType\Table;

interface DbInfo
{
    /**
     * @return Table[]
     */
    function getTableInfo(): array;
}
