<?php

namespace Bristolian\Repo\OrganisationRepo;

use Bristolian\DataType\OrganisationParam;
use Bristolian\Model\Organisation;
use Bristolian\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoOrganisationRepo implements OrganisationRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function createOrganisation(OrganisationParam $organisationParam): Organisation
    {
        $uuid = Uuid::uuid7();
        $userSQL = <<< SQL
insert into tags (
  tag_id,
  text,
  description
)
values (
  :tag_id,
  :text,
  :description
)
SQL;

        $params = [
            ':organisation_id' => $uuid->toString(),
            ':text' => $organisationParam->name,
            ':description' => $organisationParam->description



        ];

        $this->pdo_simple->insert($userSQL, $params);

        return Organisation::fromParam($uuid->toString(), $organisationParam);
    }




    /**
     * @return \Bristolian\Model\Organisation[]
     */
    public function getAllOrganisations(): array
    {
        $sql = <<< SQL
select 
  organisation_id,
  name,
  description,
  facebook_link,
  instagram_link,
  twitter_url,
  youtube_link  
from
  organisations
SQL;

        return $this->pdo_simple->fetchAllAsObject($sql, [], Organisation::class);
    }
}
