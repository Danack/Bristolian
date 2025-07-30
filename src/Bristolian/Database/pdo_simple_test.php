<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class pdo_simple_test
{
    const INSERT = <<< SQL
insert into pdo_simple_test (
    test_int,
    test_string
)
values (
    :test_int,
    :test_string
)
SQL;

    const SELECT = <<< SQL
select
    id,
    test_int,
    test_string,
    created_at
from
  pdo_simple_test 
SQL;

    const UPDATE = <<< SQL
update
  pdo_simple_test
set
  test_int = :test_int,
  test_string = :test_string
where
  id = :id
  limit 1
SQL;

}
