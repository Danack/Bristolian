<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class foi_requests
{
    const INSERT = <<< SQL
insert into foi_requests (
    description,
    foi_request_id,
    text,
    url
)
values (
    :description,
    :foi_request_id,
    :text,
    :url
)
SQL;

    const SELECT = <<< SQL
select  
    description,
    foi_request_id,
    text,
    url
from
  foi_requests
SQL;
}
