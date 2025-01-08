<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class processor
{
    const INSERT = <<< SQL
insert into processor (
    id,
    enabled,
    type
)
values (
    :id,
    :enabled,
    :type
)
SQL;

    const SELECT = <<< SQL
select
    id,
    enabled,
    type,
    updated_at
from
  processor 
SQL;
}
