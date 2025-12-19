<?php

namespace Bristolian\Repo\ProcessorRepo;

/**
 * A enum to represent the different types of background worker/job tasks.
 */
enum ProcessType: string
{
    // Worker task that generates the daily system info email
    case daily_bcc_tro = "daily_bcc_tri";

    // Worker task that generates the daily system info email
    case daily_system_info = "daily_system_info";

    // Worker task that picks up emails waiting to be sent and sends them
    case email_send = "email_send";

    // Worker task that generates the moon alerts for full moons near sunset
    case moon_alert = "moon_alert";
}
