<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

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
    text,
    created_at
from
  tag 
SQL;

    const UPDATE = <<< SQL
update
  tag
set
  description = :description,
  text = :text
where
  id = :id
  limit 1
SQL;

}
