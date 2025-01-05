<?php

// Auto-generated file do not edit

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
    type
from
  meme_tag 
SQL;
}
