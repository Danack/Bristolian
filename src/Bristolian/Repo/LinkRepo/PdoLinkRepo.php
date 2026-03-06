<?php

namespace Bristolian\Repo\LinkRepo;

use Bristolian\Database\link;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\PdoSimple\PdoSimpleWithPreviousException;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\Service\UuidGenerator\UuidGenerator;

class PdoLinkRepo implements LinkRepo
{
    public function __construct(
        private PdoSimple $pdo_simple,
        private UuidGenerator $uuidGenerator
    ) {
    }

    public function store_link(string $user_id, string $url): string
    {
        $sql = link::INSERT;

        $id = $this->uuidGenerator->generate();

        $params = [
            ':id' => $id,
            ':user_id' => $user_id,
            ':url' => $url,
        ];

        try {
            $this->pdo_simple->insert($sql, $params);
        } catch (PdoSimpleWithPreviousException $e) {
            $pdoException = $e->getPreviousPdoException();
            if ((int)$pdoException->getCode() === 23000) {
                throw new UserConstraintFailedException(
                    "Failed to insert, user constraint errored.",
                    $pdoException->getCode(),
                    $pdoException
                );
            }
            throw $e;
        }

        return $id;
    }
}
