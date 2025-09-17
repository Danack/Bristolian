<?php

declare(strict_types = 1);

function getExceptionText(\Throwable $exception): string
{
    $text = "";
    do {
        $text .= get_class($exception) . ":" . $exception->getMessage() . "\n\n";
        $text .= $exception->getTraceAsString();

        $exception = $exception->getPrevious();
    } while ($exception !== null);

    return $text;
}

/**
 * Trims exception messages to remove embedded parameters
 */
function purgeExceptionMessage(\Throwable $exception): string
{
    $rawMessage = $exception->getMessage();
    $purgeAfterPhrases = [
        // TODO - where does this come from? why would we care?
        // maybe from core PHP?
        'with params'
    ];

    $message = $rawMessage;

    foreach ($purgeAfterPhrases as $purgeAfterPhrase) {
        $matchPosition = strpos($message, $purgeAfterPhrase);
        if ($matchPosition !== false) {
            $message = substr($message, 0, $matchPosition + strlen($purgeAfterPhrase));
            $message .= '**PURGED**';
        }
    }

    return $message;
}


function getTextForException(\Throwable $exception): string
{
    $currentException = $exception;
    $text = '';

    do {
        $path = remove_install_prefix_from_path(
            $currentException->getFile() . ':' . $currentException->getLine()
        );

        $message = <<< MESSAGE
Exception type: %s
Message:  %s
File:  %s

Stack trace of previous function calls:
%s

MESSAGE;

        $text .= sprintf(
            $message,
            get_class($currentException),
            purgeExceptionMessage($currentException),
            $path,
            getFormattedException($currentException)
        );

        $currentException = $currentException->getPrevious();
    } while ($currentException !== null);

    return $text;
}

function getStacktraceForException(\Throwable $exception): string
{
    return formatLinesWithCount(getExceptionStackAsArray($exception));
}


function saneErrorHandler(
    int $errorNumber,
    string $errorMessage,
    string $errorFile,
    int $errorLine
): bool {

    $someFineConstant = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE;

    // TODO - wtf.
    // Prior to PHP 8.0.0, the error_reporting() called inside the custom
    // error handler always returned 0 if the error was suppressed by
    // the @ operator. As of PHP 8.0.0, it returns the value of this
    // (bitwise) expression: E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR |
    // E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE.
    if (error_reporting() === 0 || error_reporting() === $someFineConstant) {
        // Error reporting has been silenced
        if ($errorNumber !== E_USER_DEPRECATED) {
            // Check it isn't this value, as this is used by twig, with error suppression. :-/
            return true;
        }
    }
    if ($errorNumber === E_DEPRECATED) {
        return false;
    }
    if ($errorNumber === E_CORE_ERROR || $errorNumber === E_ERROR) {
        // For these two types, PHP is shutting down anyway. Return false
        // to allow shutdown to continue
        return false;
    }
    $message = "Error: [$errorNumber] $errorMessage in file $errorFile:$errorLine.";
    throw new \Exception($message);
}

/**
 * @param array<string, string> $trace
 * @return string
 * @throws Exception
 */
function formatTraceLine(array $trace, int $count): string
{
    $location = '??';
    if (isset($trace["file"]) && isset($trace["line"])) {
        $location = $trace["file"]. ':' . $trace["line"];
    }
    else if (isset($trace["file"])) {
        $location = $trace["file"] . ':??';
    }


    $baseDir = realpath(__DIR__ . '/../');
    if ($baseDir === false) {
        throw new \Exception("Couldn't find parent directory from " . __DIR__);
    }

    $location = str_replace($baseDir, '', $location);

    if (isset($trace["class"]) && isset($trace["type"]) && isset($trace["function"])) {
        $function = $trace["class"] . $trace["type"] . $trace["function"];
    }
    else if (isset($trace["class"]) && isset($trace["function"])) {
        $function = $trace["class"] . '_' . $trace["function"];
    }
    else if (isset($trace["function"])) {
        $function = $trace["function"];
    }
    else {
        $function = "Function is weird: " . json_encode(var_export($trace, true));
    }

    $text = sprintf(
        "#%d  %s %s\n",
        $count,
        $location,
        $function
    );

    return $text;
}


function getFormattedException(\Throwable $exception): string
{
    $output = '';

    $lines = [];

    do {
        $count = 0;
        foreach ($exception->getTrace() as $trace) {
            /* @phpstan-ignore argument.type */
            $output .= formatTraceLine($trace, $count);
            $count += 1;
        }
        $exception = $exception->getPrevious();

        // sanity limit previous exceptions to prevent
        // infinite loops.
    } while ($exception !== null && $count < 10);

    return $output;
}

/**
 * @param Throwable $exception
 * @return string[]
 */
function getExceptionStackAsArray(\Throwable $exception)
{
    $count = 0;
    $line_count = 0;
    do {
        $lines = [];
        foreach ($exception->getTrace() as $trace) {
            /* @phpstan-ignore argument.type */
            $lines[] = formatTraceLine($trace, $line_count);
            $line_count += 1;
        }
        $exception = $exception->getPrevious();

        // sanity limit previous exceptions to prevent
        // infinite loops.
        $count += 1;
    } while ($exception !== null && $count < 10);

    return $lines;
}
