<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class link
{
    const INSERT = <<< SQL
insert into link (
    id,
    url,
    user_id
)
values (
    :id,
    :url,
    :user_id
)
SQL;

    const SELECT = <<< SQL
select
    id,
    url,
    user_id
from
  link 
SQL;
}
