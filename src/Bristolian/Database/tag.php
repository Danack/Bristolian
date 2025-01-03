<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class tag
{
    const INSERT = <<< SQL
insert into tag (
    description,
    tag_id,
    text
)
values (
    :description,
    :tag_id,
    :text
)
SQL;

    const SELECT = <<< SQL
select
    description,
    tag_id,
    text
from
  tag 
SQL;
}
