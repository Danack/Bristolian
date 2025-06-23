<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class meme_tag
{
    const INSERT = <<< SQL
insert into meme_tag (
    id,
    meme_id,
    user_id,
    text,
    type
)
values (
    :id,
    :meme_id,
    :user_id,
    :text,
    :type
)
SQL;

    const SELECT = <<< SQL
select
    id,
    meme_id,
    user_id,
    text,
    type,
    created_at
from
  meme_tag 
SQL;

    const UPDATE = <<< SQL
update
  meme_tag
set
  text = :text,
  type = :type
where
  id = :id
  limit 1
SQL;
}
