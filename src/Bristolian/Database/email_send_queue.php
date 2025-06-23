<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class email_send_queue
{
    const INSERT = <<< SQL
insert into email_send_queue (
    body,
    recipient,
    retries,
    status,
    subject
)
values (
    :body,
    :recipient,
    :retries,
    :status,
    :subject
)
SQL;

    const SELECT = <<< SQL
select
    id,
    body,
    recipient,
    retries,
    status,
    subject,
    updated_at,
    created_at
from
  email_send_queue 
SQL;

    const UPDATE = <<< SQL
update
  email_send_queue
set
  body = :body,
  recipient = :recipient,
  retries = :retries,
  status = :status,
  subject = :subject
where
  id = :id
  limit 1
SQL;
}
