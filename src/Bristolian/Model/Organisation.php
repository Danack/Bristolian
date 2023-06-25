<?php

namespace Bristolian\Model;

use Bristolian\DataType\OrganisationParam;
use Bristolian\DataType\TagParam;

class Organisation
{
    public readonly string $organisation_id;
    public readonly string $name;
    public readonly string $description;
    public readonly string $homepage_link;
    public readonly string $facebook_link;
    public readonly string $instagram_link;
    public readonly string $twitter_url;
    public readonly string $youtube_link;

    public static function fromParam(
        string $uuid, OrganisationParam $organisationParam

    ): self
    {
        $instance = new self();

        $instance->organisation_id = $uuid;
        $instance->name = $organisationParam->name;
        $instance->description = $organisationParam->description;
        $instance->homepage_link = $organisationParam->homepage_link;
        $instance->facebook_link = $organisationParam->facebook_link;
        $instance->instagram_link = $organisationParam->instagram_link;
        $instance->twitter_url = $organisationParam->twitter_url;
        $instance->youtube_link = $organisationParam->youtube_link;

        return $instance;
    }

    /**
     * @return string
     */
    public function getOrganisationId(): string
    {
        return $this->organisation_id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
