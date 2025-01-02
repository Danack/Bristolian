<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class pdo_simple_test
{
    const INSERT = <<< SQL
insert into pdo_simple_test (
    id,
    test_string,
    test_int
)
values (
    :id,
    :test_string,
    :test_int
)
SQL;

    const SELECT = <<< SQL
select
    id,
    test_string,
    test_int
from
  pdo_simple_test 
SQL;
}
