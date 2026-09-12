<?php

namespace Bristolian\Service\DeployLogRenderer;

use function Safe\fopen;
use function Safe\fread;

class ProdDeployLogRenderer implements DeployLogRenderer
{
    public function render(): string
    {
        $prod_log_filename = "/var/app/deployer.log";

        if (file_exists($prod_log_filename) !== true) {
            return "Deploy log file does not exist of is not readable.";
        }

        // @codeCoverageIgnoreStart
        // I don't believe this code can be usefully unit tested
        $file = fopen($prod_log_filename, "r");

        fseek($file, -1000, SEEK_END);

        $contents = fread($file, 1000);

        $lines = explode("\n", $contents);

        $html = "<p>Log opened. Last 1000 characters are:</p>";

        $html .= "<p>";

        foreach ($lines as $line) {
            $html .= "$line <br/>";
        }

        $html .= "</p>";

        return $html;
        // @codeCoverageIgnoreEnd
    }
}
