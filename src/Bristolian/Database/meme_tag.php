<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class meme_tag
{
    const INSERT = <<< SQL
insert into meme_tag (
    id,
    user_id,
    meme_id,
    type,
    text
)
values (
    :id,
    :user_id,
    :meme_id,
    :type,
    :text
)
SQL;

    const SELECT = <<< SQL
select
    id,
    user_id,
    meme_id,
    type,
    text
from
  meme_tag 
SQL;
}
