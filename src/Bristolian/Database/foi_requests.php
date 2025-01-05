<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class foi_requests
{
    const INSERT = <<< SQL
insert into foi_requests (
    foi_request_id,
    description,
    text,
    url
)
values (
    :foi_request_id,
    :description,
    :text,
    :url
)
SQL;

    const SELECT = <<< SQL
select
    foi_request_id,
    description,
    text,
    url
from
  foi_requests 
SQL;
}
