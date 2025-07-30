<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;
adasda
class email_incoming
{
    const INSERT = <<< SQL
insert into email_incoming (
    message_id,
    body_plain,
    provider_variables,
    raw_email,
    recipient,
    retries,
    sender,
    status,
    stripped_text,
    subject
)
values (
    :message_id,
    :body_plain,
    :provider_variables,
    :raw_email,
    :recipient,
    :retries,
    :sender,
    :status,
    :stripped_text,
    :subject
)
SQL;

    const SELECT = <<< SQL
select
    id,
    message_id,
    body_plain,
    provider_variables,
    raw_email,
    recipient,
    retries,
    sender,
    status,
    stripped_text,
    subject,
    updated_at,
    created_at
from
  email_incoming 
SQL;

    const UPDATE = <<< SQL
update
  email_incoming
set
  body_plain = :body_plain,
  provider_variables = :provider_variables,
  raw_email = :raw_email,
  recipient = :recipient,
  retries = :retries,
  sender = :sender,
  status = :status,
  stripped_text = :stripped_text,
  subject = :subject
where
  id = :id
  limit 1
SQL;

}
