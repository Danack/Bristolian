<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

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

    const UPDATE = <<< SQL
update
  user_auth_email_password
set
  email_address = :email_address,
  password_hash = :password_hash
where
  id = :id
  limit 1
SQL;
}
