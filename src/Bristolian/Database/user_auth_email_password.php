<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class user_auth_email_password
{
    const INSERT = <<< SQL
insert into user_auth_email_password (
    email_address,
    password_hash,
    user_id
)
values (
    :email_address,
    :password_hash,
    :user_id
)
SQL;

    const SELECT = <<< SQL
select  
    email_address,
    password_hash,
    user_id
from
  user_auth_email_password
SQL;
}
