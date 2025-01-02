<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class foi_requests
{
    const INSERT = <<< SQL
insert into foi_requests (
    foi_request_id,
    text,
    url,
    description
)
values (
    :foi_request_id,
    :text,
    :url,
    :description
)
SQL;

    const SELECT = <<< SQL
select
    foi_request_id,
    text,
    url,
    description
from
  foi_requests 
SQL;
}
