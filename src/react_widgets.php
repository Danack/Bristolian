<?php

declare(strict_types = 1);

namespace Bristolian;

function createReactWidget(string $type, array $data): string
{
    $widget_data = [
        'initial_json_data' => $data
    ];

    [$error, $value] = convertToValue($widget_data);
    $widget_json = json_encode_safe($value);
    $widget_data = htmlspecialchars($widget_json);

    // TODO - check for errors and probably replace the content of
    // inside the span.

    $html = <<< HTML
<div>
<span class="widget_csp_violation_reports" data-widgety_json="$widget_data">
  <!-- Hello, I am a react widget. -->
</span>
</div>

HTML;

    return $html;
}
