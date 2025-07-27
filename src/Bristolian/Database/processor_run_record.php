<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class processor_run_record
{
    const INSERT = <<< SQL
insert into processor_run_record (
    debug_info,
    processor_type
)
values (
    :debug_info,
    :processor_type
)
SQL;

    const SELECT = <<< SQL
select
    id,
    debug_info,
    processor_type,
    created_at
from
  processor_run_record 
SQL;

    const UPDATE = <<< SQL
update
  processor_run_record
set
  debug_info = :debug_info,
  processor_type = :processor_type
where
  id = :id
  limit 1
SQL;
}
