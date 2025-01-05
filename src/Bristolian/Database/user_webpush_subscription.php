<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class user_webpush_subscription
{
    const INSERT = <<< SQL
insert into user_webpush_subscription (
    user_id,
    user_webpush_subscription_id,
    endpoint,
    expiration_time,
    raw
)
values (
    :user_id,
    :user_webpush_subscription_id,
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
    raw
from
  user_webpush_subscription 
SQL;
}
