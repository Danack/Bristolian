<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class link
{
    const INSERT = <<< SQL
insert into link (
    id,
    user_id,
    url
)
values (
    :id,
    :user_id,
    :url
)
SQL;

    const SELECT = <<< SQL
select
    id,
    user_id,
    url,
    created_at
from
  link 
SQL;
}
