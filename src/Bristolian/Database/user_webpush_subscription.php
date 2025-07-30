<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;
asdasd
class user_webpush_subscription
{
    const INSERT = <<< SQL
insert into user_webpush_subscription (
    user_id,
    endpoint,
    expiration_time,
    raw
)
values (
    :user_id,
    :endpoint,
    :expiration_time,
    :raw
)
SQL;

    const SELECT = <<< SQL
select
    user_id,
    user_webpush_subscription_id,
    endpoint,
    expiration_time,
    raw,
    created_at
from
  user_webpush_subscription 
SQL;

    const UPDATE = <<< SQL
update
  user_webpush_subscription
set
  endpoint = :endpoint,
  expiration_time = :expiration_time,
  raw = :raw
where
  id = :id
  limit 1
SQL;

}
