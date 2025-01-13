<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class stored_meme
{
    const INSERT = <<< SQL
insert into stored_meme (
    id,
    user_id,
    normalized_name,
    original_filename,
    size,
    state
)
values (
    :id,
    :user_id,
    :normalized_name,
    :original_filename,
    :size,
    :state
)
SQL;

    const SELECT = <<< SQL
select
    id,
    user_id,
    normalized_name,
    original_filename,
    size,
    state,
    created_at
from
  stored_meme 
SQL;
}
