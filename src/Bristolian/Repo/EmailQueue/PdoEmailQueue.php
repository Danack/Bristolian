<?php

namespace Bristolian\Repo\EmailQueue;

use Bristolian\CliController\Email as EmailController;
use Bristolian\Config\EnvironmentName;
use Bristolian\Database\email_send_queue;
use Bristolian\Model\Types\Email;
use Bristolian\PdoSimple\PdoSimple;

class PdoEmailQueue implements EmailQueue
{
    public function __construct(
        private PdoSimple $pdo
    ) {
    }

    /**
     * @param string[] $users
     * @param string $subject
     * @param string $body
     * @return void
     */
    public function queueEmailToUsers(array $users, string $subject, string $body): void
    {
        $sql = <<< SQL
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

        $this->pdo->beginTransaction();

        foreach ($users as $user) {
            $params = [
                ':body' => $body,
                ':recipient' => $user,
                ':retries' => 0,
                ':status' => EmailController::STATE_INITIAL,
                ':subject' => $subject,
            ];
            $this->pdo->insert($sql, $params);
        }

        $this->pdo->commit();
    }

    public function getEmailToSendAndUpdateState(): Email|null
    {
        $this->pdo->beginTransaction();

        $sql = email_send_queue::SELECT;

        $sql .= sprintf(
            " WHERE status in ('%s', '%s') limit 1 FOR UPDATE",
            EmailController::STATE_INITIAL,
            EmailController::STATE_RETRY
        );

        $emailOrNull = $this->pdo->fetchOneAsObjectOrNullConstructor(
            $sql,
            [],
            Email::class
        );

        if ($emailOrNull === null) {
            $this->pdo->commit();
            return null;
        }

        $sql2 = <<< SQL
update
  email_send_queue
set
   status = :status
where
  id = :id
limit 1
SQL;
        $params = [
            ':status' => EmailController::STATE_SENDING,
            ':id' => $emailOrNull->id
        ];

        $this->pdo->execute($sql2, $params);
        $this->pdo->commit();

        return $emailOrNull;
    }

    public function setEmailSent(Email $email): void
    {
        $sql = <<< SQL
update
  email_send_queue
set
   status = :status
where
  id = :id
limit 1
SQL;
        $params = [
            ':status' => EmailController::STATE_SENT,
            ':id' => $email->id
        ];

        $this->pdo->execute($sql, $params);
    }

    public function setEmailFailed(Email $email): void
    {
        $sql = <<< SQL
update
  email_send_queue
set
   status = :status
where
  id = :id
limit 1
SQL;
        $params = [
            ':status' => EmailController::STATE_FAILED,
            ':id' => $email->id
        ];

        $this->pdo->execute($sql, $params);
    }

    public function setEmailToRetry(Email $email): void
    {
        $sql = <<< SQL
update
  email_send_queue
set
   status = :status,
   retries = retries + 1
where
  id = :id
limit 1
SQL;
        $params = [
            ':status' => EmailController::STATE_RETRY,
            ':id' => $email->id
        ];
        $this->pdo->execute($sql, $params);
    }


    public function clearQueue(): int
    {
        $sql = <<< SQL
update 
  email_send_queue
set
  status = '%s'
where
  status in ('%s', '%s', '%s')
SQL;

        $sql = sprintf(
            $sql,
            EmailController::STATE_SKIPPED,
            EmailController::STATE_INITIAL,
            EmailController::STATE_SENDING,
            EmailController::STATE_RETRY,
        );

        return $this->pdo->execute($sql, []);
    }
}
