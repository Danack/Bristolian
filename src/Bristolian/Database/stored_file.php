<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class stored_file
{
    const INSERT = <<< SQL
insert into stored_file (
    id,
    normalized_name,
    original_filename,
    size,
    state,
    user_id
)
values (
    :id,
    :normalized_name,
    :original_filename,
    :size,
    :state,
    :user_id
)
SQL;

    const SELECT = <<< SQL
select  
    id,
    normalized_name,
    original_filename,
    size,
    state,
    user_id
from
  stored_file
SQL;
}
