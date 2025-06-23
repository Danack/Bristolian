<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

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

    const UPDATE = <<< SQL
update
  run_time_recorder
set
  end_time = :end_time,
  start_time = :start_time,
  status = :status,
  task = :task
where
  id = :id
  limit 1
SQL;
}
