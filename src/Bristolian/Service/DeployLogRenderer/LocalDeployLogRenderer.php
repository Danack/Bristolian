<?php

namespace Bristolian\Service\DeployLogRenderer;

class LocalDeployLogRenderer implements DeployLogRenderer
{
    public function render(): string
    {
        $html = <<< HTML
<p>I am the LocalDeployLogRenderer.</p>
<p>As the site is not deployed, there is no log.</p>
HTML;

        return $html;
    }
}
