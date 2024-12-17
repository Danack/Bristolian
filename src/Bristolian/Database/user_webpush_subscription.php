<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class user_webpush_subscription
{
    const INSERT = <<< SQL
insert into user_webpush_subscription (
    user_webpush_subscription_id,
    user_id,
    endpoint,
    expiration_time,
    raw
)
values (
    :user_webpush_subscription_id,
    :user_id,
    :endpoint,
    :expiration_time,
    :raw
)
SQL;

    const SELECT = <<< SQL
select  
    user_webpush_subscription_id,
    user_id,
    endpoint,
    expiration_time,
    raw
from
  user_webpush_subscription
SQL;
}
