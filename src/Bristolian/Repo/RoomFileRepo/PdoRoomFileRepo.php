<?php

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Model\AdminUser;
use Bristolian\Model\StoredFile;
use Bristolian\PdoSimple;
use Ramsey\Uuid\Uuid;
use Bristolian\BristolianException;
use Bristolian\Database\room_file;
use Bristolian\Database\stored_file;

class PdoRoomFileRepo implements RoomFileRepo
{
    public function __construct(
        private PdoSimple $pdoSimple
    ) {
    }

    // TODO - why are we passing IDs around and not objects?
    public function addFileToRoom(string $fileStorageId, string $room_id)
    {
        $sql = <<< SQL
insert into room_file (
    room_id,
    stored_file_id
)
values (
    :room_id,
    :stored_file_id
)
SQL;

        $params = [
            ':room_id' => $room_id,
            ':stored_file_id' => $fileStorageId,
        ];

        $this->pdoSimple->insert($sql, $params);
    }

    /**
     * @param string $room_id
     * @return array
     * @throws \ReflectionException
     */
    public function getFilesForRoom(string $room_id): array
    {
        $sql = <<< SQL
select  
    sf.id,
    sf.normalized_name,
    sf.original_filename,
    sf.state,
    sf.size,
    sf.user_id,
    sf.created_at
from
  stored_file as sf
left join
   room_file as rf
on 
 sf.id = rf.stored_file_id
where
  room_id = :room_id
SQL;
        $params = [
          ':room_id' => $room_id
        ];

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            StoredFile::class
        );
    }


    public function getFileDetails(string $room_id, string $file_id): StoredFile|null
    {
        $sql = <<< SQL
select  
    sf.id,
    sf.normalized_name,
    sf.original_filename,
    sf.state,
    sf.size,
    sf.user_id,
    sf.created_at
from
  stored_file as sf
left join
   room_file as rf
on 
 sf.id = rf.stored_file_id
where
  room_id = :room_id and
  sf.id = :file_id

SQL;
        $params = [
            ':room_id' => $room_id,
            ':file_id' => $file_id
        ];

        return $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            $params,
            StoredFile::class
        );
    }
}
