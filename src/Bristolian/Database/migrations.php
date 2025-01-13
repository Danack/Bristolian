<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class migrations
{
    const INSERT = <<< SQL
insert into migrations (
    checksum,
    description
)
values (
    :checksum,
    :description
)
SQL;

    const SELECT = <<< SQL
select
    id,
    checksum,
    description,
    created_at
from
  migrations 
SQL;
}
