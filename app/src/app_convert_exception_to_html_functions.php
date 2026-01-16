<?php

declare(strict_types = 1);

/**
 * This is a set of functions that map exceptions that are otherwise uncaught into
 * acceptable responses that will be seen by the public
 */

use Psr\Http\Message\ResponseInterface;

function renderDebuggingCaughtExceptionToHtml(
    \Bristolian\Exception\DebuggingCaughtException $pdoe,
    \Psr\Http\Message\RequestInterface $request
) {
    $text = getTextForException($pdoe);
    \error_log($text);

    return [$text, 512];
}

function renderParseErrorToHtml(
    \ParseError $parseError,
    \Psr\Http\Message\RequestInterface $request
) {
    $string = sprintf(
        "Parse error at %s:%d\n\n%s",
        remove_install_prefix_from_path($parseError->getFile()),
        $parseError->getLine(),
        $parseError->getMessage(),
    );

    $string .= "<br/>";
    $string .= getStacktraceForException($parseError);

    return [$string, 500];
}





//function renderAurynInjectionExceptionToHtml(
//    \Auryn\InjectionException $injectionException,
//    \Psr\Http\Message\RequestInterface $request
//) {
//    $text = 'Error creating dependency:<br/>';
//    foreach ($injectionException->dependencyChain as $dependency) {
//        $text .= "&nbsp;&nbsp;" . $dependency . "<br/>";
//    }
//
//    $text .= "<hr/>";
//    $text .= $injectionException->getMessage();
//
//    $text .= "<hr/>";
//    $text .= "Stacktrace: <br/>";
//
//
//    $text .= "<br/>";
//    $text .= getStacktraceForException($injectionException);
//
//    return [nl2br($text), 500];
//}

function renderMarkdownRendererException(
    \Bristolian\MarkdownRenderer\MarkdownRendererException $markdownRendererException,
    \Psr\Http\Message\RequestInterface $request
) {
    $string = sprintf(
        "MarkdownRendererException at %s:%d\n\n%s",
        remove_install_prefix_from_path($markdownRendererException->getFile()),
        $markdownRendererException->getLine(),
        $markdownRendererException->getMessage(),
    );

//    $page = createErrorPage(nl2br($string));
//    $html = createPageHtml(null, $page);

    return [$string, 500];
//    return new \SlimAuryn\Response\HtmlNoCacheResponse($html, [], 500);
}

function renderPdoSimpleWithPreviousExceptionToHtml(
    \Bristolian\PdoSimple\PdoSimpleWithPreviousException $exception,
    \Psr\Http\Message\RequestInterface $request
) {
    $text = sprintf(
        "PDO SQL Error at %s:%d\n\n%s",
        remove_install_prefix_from_path($exception->getFile()),
        $exception->getLine(),
        $exception->getMessage(),
    );

    $pdoException = $exception->getPreviousPdoException();
    
    // Try to extract SQL from stack trace
    $sql = null;
    $trace = $exception->getTrace();
    foreach ($trace as $frame) {
        // Look for PdoSimple method calls which have SQL as first argument
        if (isset($frame['class']) && 
            ($frame['class'] === 'Bristolian\PdoSimple\PdoSimple' || 
             $frame['class'] === \Bristolian\PdoSimple\PdoSimple::class) &&
            isset($frame['args']) && 
            count($frame['args']) > 0 &&
            is_string($frame['args'][0])) {
            $sql = $frame['args'][0];
            break;
        }
    }

    $text .= "<br/><br/>";
    
    if ($sql !== null) {
        $text .= "<strong>SQL Query:</strong><br/>";
        $text .= "<pre>" . htmlspecialchars($sql) . "</pre>";
        $text .= "<br/>";
    }

    // Show PDO error info
    $text .= "<strong>PDO Exception Details:</strong><br/>";
    $text .= "Code: " . htmlspecialchars((string)$pdoException->getCode()) . "<br/>";
    $text .= "Message: " . htmlspecialchars($pdoException->getMessage()) . "<br/>";
    
    // Try to get errorInfo if available (it's a property on PDOException in some PHP versions)
    if (property_exists($pdoException, 'errorInfo') && $pdoException->errorInfo !== null) {
        $errorInfo = $pdoException->errorInfo;
        if (is_array($errorInfo)) {
            if (isset($errorInfo[0])) {
                $text .= "SQLSTATE: " . htmlspecialchars((string)$errorInfo[0]) . "<br/>";
            }
            if (isset($errorInfo[1])) {
                $text .= "Error Code: " . htmlspecialchars((string)$errorInfo[1]) . "<br/>";
            }
            if (isset($errorInfo[2])) {
                $text .= "Error Message: " . htmlspecialchars((string)$errorInfo[2]) . "<br/>";
            }
        }
    }
    $text .= "<br/>";

    $text .= "<hr/>";
    $text .= "Stacktrace: <br/>";
    $text .= "<br/>";
    $text .= getStacktraceForException($exception);

    return [nl2br($text), 500];
}

//function renderUrlFetcherException(\Bristolian\UrlFetcher\UrlFetcherException $urlFetcherException)
//{
//    $string = sprintf(
//        "UrlFetcherException failed to fetch uri %s status code %d",
//        $urlFetcherException->getUri(),
//        $urlFetcherException->getStatusCode()
//    );
//
//    $page = createErrorPage(nl2br($string));
//    $html = createPageHtml(null, $page);
//
//    return new \SlimAuryn\Response\HtmlNoCacheResponse($html, [], 500);
//}



function genericExceptionHandler(\Throwable $e, \Psr\Http\Message\RequestInterface $request)
{
    $text = "Exception was thrown.";

    $exceptionStack = getExceptionStackAsArray($e);

    $text .= "<br/>";
    $text .= "<br/>";
    $text .= "Type - " . get_class($e) . "<br/>";
    $text .= "<br/>";
    $text .= "Message - " . $e->getMessage() . "<br/>";
    $text .= "<br/>";

    $text .= "<hr/>";
    $text .= "Stacktrace: <br/>";
    $text .= "<br/>";
    $text .= getStacktraceForException($e);



    return [nl2br($text), 500];
}