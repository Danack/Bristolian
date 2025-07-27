<?php

namespace Bristolian\Repo\ProcessorRepo;

enum ProcessType: string
{
    case daily_system_info = "daily_system_info";
    case email_send = "email_send";
    case moon_alert = "moon_alert";
}
