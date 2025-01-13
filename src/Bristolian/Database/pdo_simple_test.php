<?php

// Auto-generated file do not edit

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
}
