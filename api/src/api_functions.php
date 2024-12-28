<?php


function renderInjectionExceptionToJson(
    \Di\InjectionException $injectionException/*,
    \Psr\Http\Message\RequestInterface $request */
) {
    $details = [];

    $details['error'] = 'Error creating dependency';

    $details['dependency_chain'] = [];
    foreach ($injectionException->dependencyChain as $dependency) {
        $details['dependency_chain'][] = $dependency;
    }

    $details['message'] = $injectionException->getMessage();

    return $details;
}


function showTotalErrorJson(\Throwable $exception): void
{
    $details = [];
    $details['status'] = 'error';
    $message = "Failed to get exception text.";

    try {
        $message = sprintf(
            "Exception in code and Slim error handler failed also: %s %s",
            get_class($exception),
            getExceptionText($exception)
        );
        \error_log($message);
    }
    catch (\Throwable $exception) {
        $details['error_handling_error'] = $exception->getMessage();
    }

    $details['message'] = $message;

    http_response_code(503);
    echo json_encode(
        $details,
        JSON_PRETTY_PRINT
    );
}
