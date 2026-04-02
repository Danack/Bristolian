<?php

namespace Bristolian\Repo\UserRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Model\Generated\UserOwnership;
use Bristolian\Database\user;
use Bristolian\Database\user_ownership;
use Ramsey\Uuid\Uuid;

class PdoUserRepo implements UserRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {

    }


    public function getRoomUserForRoom(string $room_id): UserOwnership
    {
        $sql = user_ownership::SELECT;
        $sql .= " where type = :type and room_id = :room_id";

        $params = [
            ':type' => UserRepo::TYPE_ROOM_USER,
            ':room_id' => $room_id,
        ];

        return $this->pdo_simple->fetchOneAsObject($sql, $params, UserOwnership::class);
    }

    public function getSystemUser(): UserOwnership
    {
        $sql = user_ownership::SELECT;
        $sql .= " where type = '" . UserRepo::TYPE_SYSTEM . "'";

        return $this->pdo_simple->fetchOneAsObject($sql, [], UserOwnership::class);
    }

    public function ensureSystemUserExists(): UserOwnership
    {
        $sql = user_ownership::SELECT;
        $sql .= " where type = '" . UserRepo::TYPE_SYSTEM . "'";

        $result = $this->pdo_simple->fetchOneAsObjectOrNull($sql, [], UserOwnership::class);

        if ($result !== null) {
            return $result;
        }

        $uuid = $this->pdo_simple->insertWithUuid(user::INSERT, []);
        $params = [
            ':room_id' => null,
            ':user_id' => $uuid,
            ':type' => UserRepo::TYPE_SYSTEM,
        ];
        $this->pdo_simple->insert(user_ownership::INSERT, $params);

        return $this->pdo_simple->fetchOneAsObject($sql, [], UserOwnership::class);
    }

    public function ensureRoomUserOwnershipExistsForRoom(string $room_id): UserOwnership
    {
        $sql = user_ownership::SELECT;
        $sql .= " where type = :type and room_id = :room_id";

        $params = [
            ':type' => UserRepo::TYPE_ROOM_USER,
            ':room_id' => $room_id,
        ];

        $result = $this->pdo_simple->fetchOneAsObjectOrNull($sql, $params, UserOwnership::class);

        if ($result !== null) {
            return $result;
        }

        $uuid = $this->pdo_simple->insertWithUuid(user::INSERT, []);
        $insert_params = [
            ':room_id' => $room_id,
            ':user_id' => $uuid,
            ':type' => UserRepo::TYPE_ROOM_USER,
        ];
        $this->pdo_simple->insert(user_ownership::INSERT, $insert_params);

        return $this->pdo_simple->fetchOneAsObject($sql, $params, UserOwnership::class);
    }
}