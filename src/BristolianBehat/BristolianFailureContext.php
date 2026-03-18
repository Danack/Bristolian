<?php

declare(strict_types=1);

namespace BristolianBehat;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Session;
use Behat\Testwork\Tester\Result\TestResult;
use FailAid\Context\FailureContext;
use FailAid\Service\Output;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Rewrites Fail Aid screenshot URLs to host {@see file://} paths via {@see containerPathToHostPath()}
 * when BRISTOLIAN_HOST_PATH is set. Parent hooks use private members; this class uses reflection
 * where needed (same approach as Fail Aid’s “extend FailureContext” docs).
 */
class BristolianFailureContext extends FailureContext
{
    /**
     * @AfterStep
     */
    public function gatherStateFactsAfterFailedStep(AfterStepScope $scope): mixed
    {
        if ($scope->getTestResult()->getResultCode() !== TestResult::FAILED) {
            $this->setParentStaticProperty('exceptionHash', null);

            return null;
        }

        $message = null;

        try {
            $objectHash = spl_object_hash($scope->getTestResult()->getException());
            $exceptionHash = $this->getParentStaticProperty('exceptionHash');

            if ($exceptionHash !== $objectHash) {
                $this->setParentStaticProperty('exceptionHash', $objectHash);
                $exception = $scope->getTestResult()->getException();

                $message = '';
                if (!$this->staticCaller->call(Output::class, 'getOption', ['api'])) {
                    if ($this->staticCaller->call(Output::class, 'getOption', ['screenshot'])) {
                        try {
                            $this->invokeParentGetSession()->getPage()->getOuterHtml();
                        } catch (\WebDriver\Exception\NoSuchElement $noSuchElement) {
                            $message = PHP_EOL . PHP_EOL . 'The page is blank, is the driver/browser ready to receive the request?';
                        }
                    }

                    $session = $this->invokeParentGetSession();
                    $driver = $session->getDriver();
                    $debugBarSelectors = $this->getParentInstanceProperty('debugBarSelectors');
                    $currentScenario = $this->getParentInstanceProperty('currentScenario');

                    $gatherFacts = new ReflectionMethod(FailureContext::class, 'gatherFacts');
                    $gatherFacts->setAccessible(true);
                    $message .= $gatherFacts->invoke(
                        $this,
                        $session,
                        $driver,
                        $debugBarSelectors,
                        $scope->getFeature()->getFile(),
                        $exception->getFile(),
                        $currentScenario
                    );
                    $message = $this->rewriteScreenshotLinesInFailureMessage($message);
                } else {
                    $this->staticCaller->call(Output::class, 'setOption', ['url', false]);
                    $this->staticCaller->call(Output::class, 'setOption', ['status', false]);
                    $this->staticCaller->call(Output::class, 'setOption', ['screenshot', false]);
                    $this->staticCaller->call(Output::class, 'setOption', ['driver', false]);
                    $this->staticCaller->call(Output::class, 'setOption', ['rerun', false]);

                    $currentScenario = $this->getParentInstanceProperty('currentScenario');

                    $message = $this->staticCaller->call(Output::class, 'getExceptionDetails', [
                        null,
                        null,
                        $scope->getFeature()->getFile(),
                        $exception->getFile(),
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        $currentScenario,
                    ]);
                }

                /** @var array<string, mixed> $states */
                $states = $this->getParentStaticProperty('states');
                $addStateDetails = new ReflectionMethod(FailureContext::class, 'addStateDetails');
                $addStateDetails->setAccessible(true);
                $message = $addStateDetails->invoke(
                    $this,
                    $message,
                    $this->getStateDetails($states)
                );

                $setDetails = new ReflectionMethod(FailureContext::class, 'setAdditionalExceptionDetailsInException');
                $setDetails->setAccessible(true);
                $setDetails->invoke($this, $exception, $message);
            }

            $waitOnFailure = $this->getParentStaticProperty('waitOnFailure');
            if ($waitOnFailure) {
                echo sprintf('Waiting on failure for %d seconds', $waitOnFailure) . PHP_EOL;
            }

            $feedbackOnFailure = $this->getParentStaticProperty('feedbackOnFailure');
            if ($feedbackOnFailure) {
                echo PHP_EOL . '-- FAIL --' . PHP_EOL . $scope->getTestResult()->getException()->getMessage();
                ob_flush();
            }

            return $message;
        } catch (DriverException $driverException) {
            echo 'Error message: ' . $driverException->getMessage();
        }

        $this->setParentStaticProperty('exceptionHash', null);

        return null;
    }

    private function invokeParentGetSession(?string $name = null): Session
    {
        $method = new ReflectionMethod(FailureContext::class, 'getSession');
        $method->setAccessible(true);

        return $method->invoke($this, $name);
    }

    /**
     * @return mixed
     */
    private function getParentStaticProperty(string $propertyName): mixed
    {
        $property = new ReflectionProperty(FailureContext::class, $propertyName);
        $property->setAccessible(true);

        return $property->getValue();
    }

    private function setParentStaticProperty(string $propertyName, mixed $value): void
    {
        $property = new ReflectionProperty(FailureContext::class, $propertyName);
        $property->setAccessible(true);
        $property->setValue(null, $value);
    }

    /**
     * @return mixed
     */
    private function getParentInstanceProperty(string $propertyName): mixed
    {
        $property = new ReflectionProperty(FailureContext::class, $propertyName);
        $property->setAccessible(true);

        return $property->getValue($this);
    }

    private function rewriteScreenshotLinesInFailureMessage(string $message): string
    {
        return (string) preg_replace_callback(
            '/\[SCREENSHOT\] ([^\r\n]+)/',
            function (array $matches): string {
                $rewritten = $this->rewriteScreenshotUrlForHost(trim($matches[1]));

                return '[SCREENSHOT] ' . $rewritten;
            },
            $message
        );
    }

    private function rewriteScreenshotUrlForHost(string $url): string
    {
        if (!str_starts_with($url, 'file://')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return $url;
        }

        if (!function_exists(__NAMESPACE__ . '\\containerPathToHostPath')) {
            return $url;
        }

        try {
            $hostPath = containerPathToHostPath($path);
        } catch (\InvalidArgumentException) {
            return $url;
        }

        if ($hostPath === null) {
            return $url;
        }

        return 'file://' . str_replace(' ', '%20', $hostPath);
    }
}
