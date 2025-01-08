<?php

namespace Bristolian\Repo\ProcessorRepo;

enum ProcessType: string
{
    case email_send = "email_send";
    case moon_alert = "moon_alert";
}
