<?php

namespace Bristolian\Service\DeployLogRenderer;

class ProdDeployLogRenderer implements DeployLogRenderer
{
    public function render(): string
    {
        $prod_log_filename = "/var/log/deployer/bristolian.log";

        if (file_exists($prod_log_filename) !== true){
            return "Deploy log file does not exist of is not readable.";
        }

        $file = fopen($prod_log_filename, "r");

        if ($file === false) {
            return "Failed to open deploy log file.";
        }

        fseek($file, -1000, SEEK_END);

        $contents = fread($file, 1000);

        if ($contents === false) {
            return "Deploy log opened, but failed to read contents.";
        }

        $lines = explode("\n", $contents);

        $html = "Log opened. Last 1000 characters are:";

        foreach ($lines as $line) {
            $html = "<p>$line</p>";
        }

        return $html;
    }
}