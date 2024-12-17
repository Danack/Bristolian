<?php

namespace Bristolian\Repo\LinkRepo;

use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;
use Bristolian\PdoSimple;
use Bristolian\Database\link;

class PdoLinkRepo implements LinkRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function store_link(string $user_id, string $url): string
    {
        $sql = link::INSERT;

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            ':id' => $id,
            ':user_id' => $user_id,
            ':url' => $url,
        ];

        try {
            $this->pdo_simple->insert($sql, $params);
        }
        catch (\PDOException $pdoException) {
            // TODO - technically, this should check the message also.
            if ((int)$pdoException->getCode() === 23000) {
                throw new UserConstraintFailedException(
                    "Failed to insert, user constraint errored.",
                    $pdoException->getCode(),
                    $pdoException
                );
            }

            // Rethrow original exception as it wasn't a failure to insert.
            throw $pdoException;
        }

        return $id;
    }
}
