<?php

declare(strict_types = 1);

namespace BristolianBehat;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
//use Behat\Mink\Exception\ResponseTextException;
//use Behat\Mink\Element\NodeElement;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

//use Behat\Mink\Element\DocumentElement;
//use Osf\App;
//use Osf\Model\PurchaseOrder;
//use OsfTest\Repo\Testing;
//use OsfTest\Repo\TesterMcTesterSon;

//BeforeSuite
//AfterSuite
//
//BeforeFeature
//AfterFeature
//
//BeforeScenario
//AfterScenario
//
//BeforeStep
//AfterStep


class AdminContext extends MinkContext
{
//    use Testing;

//    private static $scopeData = [];
//
//    private static $featureData = [];
//
//    /** @var \OsfTest\Repo\TesterMcTesterSon */
//    private static $testing;

    public function __construct()
    {
        $this->setMinkParameter('base_url', 'http://local.bristolian.com');
    }

    /**
     * @BeforeSuite
     */
    public static function prepareSuite(BeforeSuiteScope $scope): void
    {
        // prepare system for test suite
        // self::$scopeData; // commented out - undefined property
    }

//    /**
//     * @param string $name
//     * @return mixed
//     */
//    private function getFeatureData($name)
//    {
//        // if (array_key_exists($name, self::$featureData) !== true) {
//        //     throw new \Exception("featureData [$name] is not set, cannot use it");
//        // }
//        // return self::$featureData[$name];
//        throw new \Exception("getFeatureData is not implemented - self::\$featureData is commented out");
//    }

    /**
     * @BeforeScenario
     */
    public function beforeScenarios(BeforeScenarioScope $scope): void
    {
        $this->setMinkParameter('base_url', 'http://local.admin.opensourcefees.com');
//        // Load and save the environment for each scenario.
//        $this->environment = $scope->getEnvironment();
//        // Set the base URL. Can be overridden check @see.
//        $base_url = $this->parameters['base_url'];
//        $this->setBaseUrl('http://local.admin.opensourcefees.com');
    }

//    private function setBaseUrl($url)
//    {
//        $this->environment = $scope->getEnvironment();
//
//        $this->getMinkParameters()
//
//        foreach ($this->getMinkParameters()->getContexts() as $context) {
//            if ($context instanceof \Behat\MinkExtension\Context\RawMinkContext) {
//                $context->setMinkParameter('base_url', $url);
//            }
//        }
//    }

//    /**
//     * @BeforeFeature
//     */
//    public static function prepare(BeforeFeatureScope $scope)
//    {
//        // prepare system for test feature
//        self::$featureData = [];
//        self::$testing = new TesterMcTesterSon();
//    }
//
//    /**
//     * Take screenshot when step fails.
//     * Works only with Selenium2Driver.
//     *
//     * @AfterStep
//     */
//    public function takeScreenshotAfterFailedStep(AfterStepScope $event)
//    {
//        if (!$event->getTestResult()->isPassed()) {
//            $this->takeScreenshot($event, $event->getFeature()->getTitle());
//        }
//    }

//    private function takeScreenshot(AfterStepScope $event, $title)
//    {
//        $screenshot = $this->getSession()->getDriver()->getScreenshot();
//
//        $filename = sprintf(
//            __DIR__ . "/../../../screenshot/admin_%s_%d.png",
//            str_replace(' ', '_', $title),
//            $event->getStep()->getLine()
//        );
//
//        @mkdir(dirname($filename), 0755, true);
//
//        file_put_contents($filename, $screenshot);
//    }
//
//    public function takeDebugScreenshot()
//    {
//        $screenshot = $this->getSession()->getDriver()->getScreenshot();
//        $filename = __DIR__ . "/../../../test/debug_Gaaag.png";
//        @mkdir(dirname($filename), 0755, true);
//        file_put_contents($filename, $screenshot);
//    }
//
//    public function getNumberOfIframes()
//    {
//        $session = $this->getSession();
//        $page = $session->getPage();
//
//        $iframeNodes = $page->findAll('css', 'iframe');
//
//        return count($iframeNodes);
//    }


//    /**
//     * @return \Behat\Mink\Element\NodeElement
//     */
//    private function findById(string $id)
//    {
//        $session = $this->getSession();
//        $page = $session->getPage();
//        $element = $page->findById($id);
//
//        if ($element === null) {
//            throw new \Exception("Failed to find element with id: " . $id);
//        }
//
//        return $element;
//    }

    /**
     * @return \Behat\Mink\Element\NodeElement[]
     */
    protected function findAll(string $selector, string $locator)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        return $page->findAll($selector, $locator);
    }

//    /**
//     * @Given /^I am logged in as an admin$/
//     */
//    public function iAmLoggedInAsAnAdmin()
//    {
//        $this->setMinkParameter('base_url', 'http://local.admin.opensourcefees.com');
//        $this->visitPath('/');
//
//        $currentUrl = $this->getSession()->getCurrentUrl();
//        $path = parse_url($currentUrl, PHP_URL_PATH);
//
//        if ($path === null) {
//            throw new \Exception('failed to get path from ' . $currentUrl);
//        }
//
//        if ($path === '/') {
//            // Yay already logged in.
//            return;
//        }
//        else if (strpos($path, '/login') === 0) {
//            $this->fillField('username', App::TEST_ADMIN_USERNAME);
//            $this->fillField('password', App::TEST_ADMIN_PASSWORD);
//            $this->pressButton('Login');
//        }
//        else {
//            throw new \Exception("On unexpected path [$path].");
//        }
//
//        $this->visitPath('/');
//        $currentUrl = $this->getSession()->getCurrentUrl();
//        $path = parse_url($currentUrl, PHP_URL_PATH);
//        if ($path !== '/') {
//            throw new \Exception("Failed to login. Path is [$path].");
//        }
//    }

//    /**
//     * @Given /^A purchase order has been placed$/
//     */
//    public function aPurchaseOrderHasBeenPlaced()
//    {
//        $nameOnPurchaseOrder = 'John Testing_' . time();
//        $email_address = 'John_'. time() . '@example.com';
//
//        self::$featureData['purchase_order'] = self::$testing->createPurchaseOrderForProject(
//            'Imagick',
//            $nameOnPurchaseOrder,
//            $email_address
//        );
//
//        self::$featureData['purchase_order_name'] = $nameOnPurchaseOrder;
//        self::$featureData['purchase_order_email_address'] = $email_address;
//    }


//    /**
//     * @Given /^I should see that purchase order and link and click it$/
//     */
//    public function iShouldSeeThatPurchaseOrderAndLink()
//    {
//        $this->assertPageContainsText($this->getFeatureData('purchase_order_name'));
//        $this->assertPageContainsText($this->getFeatureData('purchase_order_email_address'));
//
//        /** @var PurchaseOrder $purchase_order */
//        $purchase_order = $this->getFeatureData('purchase_order');
//
//        $routeInfo = self::$testing->createRouteInfo();
//
//        $link = $routeInfo->getPurchaseOrderDetailsUrl($purchase_order);
//
//        $links = $this->findAll('xpath', "//a[@href='$link']");
//
//        foreach ($links as $link) {
//            $this->clickLink($link);
//            return;
//        }
//
//        throw new \Exception("Failed to find purchase order link");
//    }


//    /**
//     * @Then /^I should see the details of that purchase order$/
//     */
//    public function iShouldSeeTheDetailsOfThatPurchaseOrder()
//    {
//        /** @var PurchaseOrder $purchase_order */
//        $purchase_order = $this->getFeatureData('purchase_order');
//
//
//        $this->assertPageContainsText($purchase_order->getName());
//    }

//    public function featureEmail()
//    {
//        if (!array_key_exists('email', self::$featureData)) {
//            self::$featureData['email'] = 'test' . time() . '@basereality.com';
//        }
//
//        return self::$featureData['email'];
//    }
//
//    public function featureCountry()
//    {
//        if (!array_key_exists('country', self::$featureData)) {
//            self::$featureData['country'] = 'country_' . time();
//        }
//
//        return self::$featureData['country'];
//    }

//
//    public function fillField($field, $value)
//    {
//        $field = $this->fixStepArgument($field);
//        $value = $this->fixStepArgument($value);
//        $this->getSession()->getPage()->fillField($field, $value);
//    }

//    /**
//     * Returns fixed step argument (with \\" replaced back to ")
//     *
//     * @param string $argument
//     *
//     * @return string
//     */
//    protected function fixStepArgument($argument)
//    {
//        return str_replace('\\"', '"', $argument);
//    }

//    public function pressButton($button)
//    {
//        $button = $this->fixStepArgument($button);
//        $this->getSession()->getPage()->pressButton($button);
//    }
//
//    public function assertPageContainsText($text)
//    {
//        $this->assertSession()->pageTextContains($this->fixStepArgument($text));
//    }
}
