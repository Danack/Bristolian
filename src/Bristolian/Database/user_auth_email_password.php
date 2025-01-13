<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class user_auth_email_password
{
    const INSERT = <<< SQL
insert into user_auth_email_password (
    user_id,
    email_address,
    password_hash
)
values (
    :user_id,
    :email_address,
    :password_hash
)
SQL;

    const SELECT = <<< SQL
select
    user_id,
    email_address,
    password_hash,
    created_at
from
  user_auth_email_password 
SQL;
}
