<?php

declare(strict_types = 1);

namespace Bristolian;

function createReactWidget(string $type, array $data): string
{
    $data = convertToValue($data);

    $widget_data = json_encode_safe($data);

    $html = <<< HTML
<div>
<span class="widget_csp_violation_reports" data-widget_data="$widget_data">
  Hello, I am a react widget.
</span>
</div>

HTML;

    return $html;
}