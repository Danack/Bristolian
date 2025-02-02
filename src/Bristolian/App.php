<?php

declare(strict_types = 1);

namespace Bristolian;

/**
 * Mostly used for app wide constants.
 */
class App
{
    const DATE_TIME_FORMAT = 'Y_m_d_H_i_s';

    const MYSQL_DATE_TIME_FORMAT = "Y-m-d H:i:s";

    const INVOICE_DATE_FORMAT = 'F jS, Y';

    const DATE_TIME_EXACT_FORMAT = "Y-m-d\TH:i:s.uP";

    const DATE_TIME_FORMAT_READABLE = 'Y/m/d g:i a';

    const FLASH_MESSAGE_ERROR = 'flash_message_error';

    const FLASH_MESSAGE_SUCCESS = 'flash_message_success';

    const ADMIN_PROJECT_SELECTED = 'admin_project_selected';

    const ADMIN_USERNAME = 'admin_username';

    const YAY_PAGE_OK = '<!-- yay, page is done. -->';

    const MAX_MEME_FILE_SIZE = 50 * 1024 * 1024;

    const ERROR_CAUGHT_BY_MIDDLEWARE_MESSAGE = "<!-- This is caught in the exception mapper -->";

    // This is used by the tests to check that exception handling is working properly
    const ERROR_CAUGHT_BY_MIDDLEWARE_API_MESSAGE = "Correctly caught DebuggingCaughtException";

    // These are only available in test environments, where the DB
    // has been seeded with test data.
    const TEST_ADMIN_USERNAME = "admin@example.com";
    const TEST_ADMIN_PASSWORD = 'password12345';
    public const ENVIRONMENT_LOCAL = 'local';
    public const ENVIRONMENT_PROD = 'prod';

    const CSP_REPORT_PATH = '/api/csp/violation';
}
