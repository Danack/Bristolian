<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class tag
{
    const INSERT = <<< SQL
insert into tag (
    tag_id,
    description,
    text
)
values (
    :tag_id,
    :description,
    :text
)
SQL;

    const SELECT = <<< SQL
select
    tag_id,
    description,
    text
from
  tag 
SQL;
}
