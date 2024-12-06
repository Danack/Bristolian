<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class user_webpush_subscription
{
    const INSERT = <<< SQL
insert into user_webpush_subscription (
    endpoint,
    expiration_time,
    raw,
    user_id,
    user_webpush_subscription_id
)
values (
    :endpoint,
    :expiration_time,
    :raw,
    :user_id,
    :user_webpush_subscription_id
)
SQL;

    const SELECT = <<< SQL
select  
    endpoint,
    expiration_time,
    raw,
    user_id,
    user_webpush_subscription_id
from
  user_webpush_subscription
SQL;
}
