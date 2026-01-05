<?php

namespace Bristolian\Repo\FoiRequestRepo;

use Bristolian\Database\foi_requests;
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
        $sql = foi_requests::SELECT;
        $sql .= " where foi_request_id = :foi_request_id limit 1";

        return $this->pdo_simple->fetchOneAsObject(
            $sql,
            [':foi_request_id' => $id],
            FoiRequest::class
        );
    }

    public function createFoiRequest(FoiRequestParams $foiRequestParam): FoiRequest
    {
        $uuid = Uuid::uuid7();
        $sql = foi_requests::INSERT;

        $params = [
            ':foi_request_id' => $uuid->toString(),
            ':text' => $foiRequestParam->text,
            ':url' => $foiRequestParam->url,
            ':description' => $foiRequestParam->description
        ];

        $this->pdo_simple->insert($sql, $params);

        return FoiRequest::fromParam($uuid->toString(), $foiRequestParam);
    }

    /**
     * @return \Bristolian\Model\FoiRequest[]
     */
    public function getAllFoiRequests(): array
    {
        $sql = foi_requests::SELECT;

        return $this->pdo_simple->fetchAllAsObject($sql, [], FoiRequest::class);
    }
}
