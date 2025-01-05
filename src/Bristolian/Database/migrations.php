<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class migrations
{
    const INSERT = <<< SQL
insert into migrations (
    id,
    checksum,
    description
)
values (
    :id,
    :checksum,
    :description
)
SQL;

    const SELECT = <<< SQL
select
    id,
    checksum,
    description
from
  migrations 
SQL;
}
