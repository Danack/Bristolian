<?php


function renderInjectionExceptionToHtml(
    \Di\InjectionException $injectionException
) {
    $text = 'Error creating dependency:<br/>';
    foreach ($injectionException->dependencyChain as $dependency) {
        $text .= "&nbsp;&nbsp;" . $dependency . "<br/>";
    }

    $text .= "<hr/>";
    $text .= $injectionException->getMessage();

    $text .= "<hr/>";
    $text .= "Stacktrace: <br/>";

    $text .= "<br/>";
    $text .= getStacktraceForException($injectionException);

    return [nl2br($text), 500];
}


function showTotalErrorPage(\Throwable $exception): void
{
    $exceptionText = "Failed to get exception text.";

    try {
        $exceptionText = getExceptionText($exception);
        $message = sprintf(
            "Exception in code and Slim error handler failed also: %s %s.",
            get_class($exception),
            $exceptionText
        );

        \error_log($message);
    }
    catch (\Throwable $exception) {
        // Does nothing.
        \error_log("showTotalErrorPage failed: " . $exception->getMessage());
    }

    http_response_code(503);

    if ($exceptionText !== null) {
        var_dump(get_class($exception));
        echo nl2br($exceptionText);
    }
}
