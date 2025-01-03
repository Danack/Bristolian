<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class meme_tag
{
    const INSERT = <<< SQL
insert into meme_tag (
    id,
    meme_id,
    text,
    type,
    user_id
)
values (
    :id,
    :meme_id,
    :text,
    :type,
    :user_id
)
SQL;

    const SELECT = <<< SQL
select
    id,
    meme_id,
    text,
    type,
    user_id
from
  meme_tag 
SQL;
}
