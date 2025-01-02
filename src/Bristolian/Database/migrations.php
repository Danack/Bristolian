<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class migrations
{
    const INSERT = <<< SQL
insert into migrations (
    id,
    description,
    checksum
)
values (
    :id,
    :description,
    :checksum
)
SQL;

    const SELECT = <<< SQL
select
    id,
    description,
    checksum
from
  migrations 
SQL;
}
