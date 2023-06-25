<?php

namespace Bristolian\Repo\OrganisationRepo;

use Bristolian\DataType\OrganisationParam;
use Bristolian\Model\Organisation;

interface OrganisationRepo
{
    /**
     * @return \Bristolian\Model\Organisation[]
     */
    public function getAllOrganisations(): array;

    public function createOrganisation(OrganisationParam $organisationParam): Organisation;
}
