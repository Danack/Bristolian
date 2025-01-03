<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class migrations
{
    const INSERT = <<< SQL
insert into migrations (
    checksum,
    description,
    id
)
values (
    :checksum,
    :description,
    :id
)
SQL;

    const SELECT = <<< SQL
select
    checksum,
    description,
    id
from
  migrations 
SQL;
}
