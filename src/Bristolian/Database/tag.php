<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class tag
{
    const INSERT = <<< SQL
insert into tag (
    tag_id,
    text,
    description
)
values (
    :tag_id,
    :text,
    :description
)
SQL;

    const SELECT = <<< SQL
select  
    tag_id,
    text,
    description
from
  tag
SQL;
}
