<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class pdo_simple_test
{
    const INSERT = <<< SQL
insert into pdo_simple_test (
    id,
    test_int,
    test_string
)
values (
    :id,
    :test_int,
    :test_string
)
SQL;

    const SELECT = <<< SQL
select  
    id,
    test_int,
    test_string
from
  pdo_simple_test
SQL;
}
