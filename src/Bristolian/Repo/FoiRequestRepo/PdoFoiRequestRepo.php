<?php

namespace Bristolian\Repo\FoiRequestRepo;

use Bristolian\Parameters\FoiRequestParams;
use Bristolian\Model\FoiRequest;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoFoiRequestRepo implements FoiRequestRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function getById(string $id): FoiRequest
    {
        $sql = <<< SQL
select 
  foi_request_id,
  text,
  url,
  description
from
  foi_requests
where 
  foi_request_id = :foi_request_id
limit 1
SQL;

        return $this->pdo_simple->fetchOneAsObject(
            $sql,
            [':foi_request_id' => $id],
            FoiRequest::class
        );
    }

    public function createFoiRequest(FoiRequestParams $foiRequestParam): FoiRequest
    {
        $uuid = Uuid::uuid7();
        $userSQL = <<< SQL
insert into foi_requests (
  foi_request_id,
  text,
  url,
  description
)
values (
  :foi_request_id,
  :text,
  :url,
  :description
)
SQL;

        $params = [
            ':foi_request_id' => $uuid->toString(),
            ':text' => $foiRequestParam->text,
            ':url' => $foiRequestParam->url,
            ':description' => $foiRequestParam->description
        ];

        $this->pdo_simple->insert($userSQL, $params);

        return FoiRequest::fromParam($uuid->toString(), $foiRequestParam);
    }

    /**
     * @return \Bristolian\Model\FoiRequest[]
     */
    public function getAllFoiRequests(): array
    {
        $sql = <<< SQL
select 
  foi_request_id,
  text,
  url,
  description
from
  foi_requests
SQL;

        return $this->pdo_simple->fetchAllAsObject($sql, [], FoiRequest::class);
    }
}
