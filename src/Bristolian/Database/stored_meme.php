<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;
asdasd
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

    const UPDATE = <<< SQL
update
  stored_meme
set
  normalized_name = :normalized_name,
  original_filename = :original_filename,
  size = :size,
  state = :state
where
  id = :id
  limit 1
SQL;

}
