<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class run_time_recorder
{
    const INSERT = <<< SQL
insert into run_time_recorder (
    end_time,
    start_time,
    status,
    task
)
values (
    :end_time,
    :start_time,
    :status,
    :task
)
SQL;

    const SELECT = <<< SQL
select
    id,
    end_time,
    start_time,
    status,
    task
from
  run_time_recorder 
SQL;
}
