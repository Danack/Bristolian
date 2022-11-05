<?php

class InfoRequest
{
    public function __construct(
        //: "actions_taken_since_2017_to_impr",
        private string $url_title,

        //: "Actions taken since 2017 to improve air quality and tackle air pollutionin Bristol",
    private string $title,

    //: "Awaiting classification.",
    private string $display_status,

    //: "waiting_response";
    private RequestState $described_state,

    //: "2022-10-15T13:11:56.603+01:00",
    private DateTimeInterface $created_at,
        //: "2022-11-02T12:32:38.700+00:00",
    private DateTimeInterface $updated_at
    ){
    }



    public function getUrlTitle(): string
    {
        return $this->url_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDisplayStatus(): string
    {
        return $this->display_status;
    }

    public function getDescribedState(): RequestState
    {
        return $this->described_state;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updated_at;
    }
}