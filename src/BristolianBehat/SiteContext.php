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
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
//use Behat\Mink\Element\DocumentElement;
//use Osf\Repo\StripeCheckoutSessionRepo\StripeCheckoutSessionRepo;
//use Osf\Model\StripeCheckoutSession;
//use Osf\Repo\StripeCheckoutSessionRepo\DatabaseStripeCheckoutSessionRepo;

//require_once __DIR__ . '/../../../test/phpunit_bootstrap.php';

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


class SiteContext extends MinkContext
{
//    private static $scopeData = [];
//
//    private static $featureData = [];
//
////    /** @var \Auryn\Injector */
////    private $injector;
//
//    /** @var int */
//    private static $start_time;

    /**
     * @BeforeSuite
     */
    public static function prepareSuite(BeforeSuiteScope $scope): void
    {
//        // prepare system for test suite
//        self::$scopeData = [];
//        self::$start_time = time();
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenarios(BeforeScenarioScope $scope): void
    {
         $this->setMinkParameter('base_url', 'http://local.bristolian.org/');
    }

    /**
     * @BeforeFeature
     */
    public static function prepare(BeforeFeatureScope $scope): void
    {
//        // prepare system for test feature
//        self::$featureData = [];
    }

    /**
     * Take screenshot when step fails.
     * Works only with Selenium2Driver.
     *
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep(AfterStepScope $event): void
    {
        if (!$event->getTestResult()->isPassed()) {
            $this->takeScreenshot($event, $event->getFeature()->getTitle());
        }
    }

    private function takeScreenshot(AfterStepScope $event, string $title): void
    {
        $screenshot = $this->getSession()->getDriver()->getScreenshot();
        $filename = sprintf(
            __DIR__ . "/../../screenshot/screenshot_%s_%d.png",
            str_replace(' ', '_', $title),
            $event->getStep()->getLine()
        );

        @mkdir(dirname($filename), 0755, true);

        file_put_contents($filename, $screenshot);
    }



    public function takeDebugScreenshot(): void
    {
        echo "Should be taking screenshot...\n";
        $screenshot = $this->getSession()->getDriver()->getScreenshot();
        $filename = __DIR__ . "/../../screenshot/debug_Gaaag.png";
        @mkdir(dirname($filename), 0755, true);
        file_put_contents($filename, $screenshot);
    }

    public function getNumberOfIframes(): int
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $iframeNodes = $page->findAll('css', 'iframe');

        return count($iframeNodes);
    }

//    /**
//     * @Given /^I wait to see "([^"]*)"$/
//     */
//    public function iWaitToSee($text)
//    {
//        $numberOfIframes = $this->getNumberOfIframes();
//        for ($attempts=0; $attempts<10; $attempts++) {
//            for ($iframe=0; $iframe< $numberOfIframes; $iframe++) {
//                /** @var $iframe string */
//                $this->getSession()->getDriver()->switchToIFrame($iframe);
//                try {
//                    $this->assertSession()->pageTextContains($this->fixStepArgument($text));
//                    return true;
//                } catch (ResponseTextException $rte) {
//                    // not found
//                }
//            }
//            sleep(1);
//        }
//        throw new \Exception("Failed to see [$text] on page.");
//
//        // TODO - switch back.
//        // $this->getSession()->getDriver()->switchToIFrame(0);
//    }

//    /**
//     * @Given /^see the payment widget for a project$/
//     */
//    public function seeThePaymentWidget()
//    {
//        $session = $this->getSession();
//        $page = $session->getPage();
//        $forms = $page->findAll('xpath', '//span[contains(@class,\'osf_payment_widget\')]');
//
//        if (count($forms) === 0) {
//            throw new \Exception("Failed to find payment form.");
//        }
//
//        if (count($forms) > 1) {
//            throw new \Exception("More than one form on screen.");
//        }
//    }
//
//    private function findById(string $id)
//    {
//        $session = $this->getSession();
//        $page = $session->getPage();
//        $element = $page->findById($id);
//
//        if ($element === null) {
//            throw new \Exception("Failed to find " . $element);
//        }
//
//        return $element;
//    }

//    /**
//     * @Then /^the total price should be ([^"]*)$/
//     */
//    public function theTotalPriceShouldBe($arg1)
//    {
//        $element = $this->findById('osf_total_price');
//
//        $currentHtml = $element->getHtml();
//        if ($currentHtml !== $arg1) {
//            throw new \Exception("Total price is set to [$currentHtml] not [$arg1]");
//        }
//    }
//
//    /**
//     * @Given /^I set all the quantities to zero$/
//     */
//    public function iSetAllTheQuantitiesToZero()
//    {
//        $quantityElements = $this->findQuantityElements();
//
//        foreach ($quantityElements as $quantityElement) {
//            $quantityElement->setValue('0');
//        }
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
//     * @Then /^the stripe keys should be set in the datalayer$/
//     */
//    public function theStripeKeysShouldBeSetInTheDatalayer()
//    {
//        $js = <<< JS
//
//(function (){
//var stripe_platform_public_key = 'not set';
//var stripe_connected_account_id = 'not set';
//var stripe_project = 'not set';
//
//if (window.osf_dataLayer.stripe === undefined) {
//    stripe_platform_public_key = 'datalayer is missing stripe.';
//}
//else {
//if (window.osf_dataLayer.stripe.public_key === undefined) {
//  stripe_platform_public_key = 'undefined';
//}
//else {
//  stripe_platform_public_key = window.osf_dataLayer.stripe.public_key;
//}
//
//if (window.osf_dataLayer.stripe.account_id === undefined) {
//  stripe_connected_account_id = 'undefined';
//}
//else {
//  stripe_connected_account_id = window.osf_dataLayer.stripe.account_id;
//}
//
//
//if (window.osf_dataLayer.stripe.project_name === undefined) {
//  stripe_project = 'undefined';
//}
//else {
//  stripe_project = window.osf_dataLayer.stripe.project_name;
//}
//
//}
//
//      return [
//        stripe_platform_public_key,
//        stripe_connected_account_id,
//        stripe_project
//      ];
//      })();
//JS;
//
//        [
//            $stripe_platform_public_key,
//            $stripe_connected_account_id,
//            $stripe_project
//        ] = $this->getSession()->evaluateScript($js);
//
////        throw new \Exception(var_export($result, true));
//
//        if (is_string($stripe_platform_public_key) !== true) {
//            throw new \Exception("stripe_platform_public_key is not set correctly.");
//        }
//        if (strpos($stripe_platform_public_key, 'pk_') !== 0) {
//            throw new \Exception("stripe_platform_public_key does not start with pk_.");
//        }
//
//        if (is_string($stripe_connected_account_id) !== true) {
//            throw new \Exception("stripe_connected_account_id is not set correctly.");
//        }
//        if (strpos($stripe_connected_account_id, 'acct_') !== 0) {
//            throw new \Exception("stripe_connected_account_id does not start with acct_: " . $stripe_platform_public_key);
//        }
//        if ($stripe_project !== 'Imagick') {
//            throw new \Exception("stripe_project is wrong: " . var_export($stripe_project, true));
//        }
//        // ok
//    }

//    /**
//     * @Then /^I should see a sku name$/
//     */
//    public function iShouldSeeASkuName()
//    {
//        $skuName = 'Maintenance';
//
//        $this->assertSession()->pageTextContains($skuName);
//    }


//    /**
//     * @Then /^I should see a sku name$/
//     */
//    public function iShouldSeeASkuName()
//    {
//        $skuName = 'Maintenance';
//
//        $this->assertSession()->pageTextContains($skuName);
//    }
//
//    /**
//     * @Given /^I should see a sku description$/
//     */
//    public function iShouldSeeASkuDescription()
//    {
//        $description = 'Ongoing bugfixes and support of new Imagick versions';
//
//        $this->assertSession()->pageTextContains($description);
//    }

//    /**
//     * @Then /^I should be on the app domain within (\d+) seconds$/
//     */
//    public function iShouldBeOnTheAppDomainWithinSeconds($seconds)
//    {
//        $expectedDomain = getConfig(\Osf\Config::STRIPE_PLATFORM_APP_DOMAIN);
//        $expectedHost = parse_url($expectedDomain, PHP_URL_HOST);
//        return $this->iShouldBeOnTheDomainWithinSeconds($expectedHost, $seconds);
//    }

    /**
     * @Then /^I should be on the domain "([^"]*)" within (\d+) seconds$/
     */
    public function iShouldBeOnTheDomainWithinSeconds(string $expectedDomain, int $seconds): void
    {
        $startTime = microtime(true);

        do {
            $currentUrl = $this->getSession()->getCurrentUrl();
            $host = parse_url($currentUrl, PHP_URL_HOST);
            if (strcasecmp($host, $expectedDomain) === 0) {
                return;
            }

            usleep(100 * 1000);//tenth of a second

            $currentTime = microtime(true);
            $timeElapsed = $currentTime - $startTime;
            if ($timeElapsed > $seconds) {
                throw new \Exception("Didn't reach domain [$expectedDomain] within [$seconds] seconds.");
            }
        }
        while (1);
    }

//    /**
//     * @Given /^I click the Stripe Pay button$/
//     */
//    public function iClickTheStripePayButton()
//    {
//        $quantityIncrementElements = $this->findAll(
//            'xpath',
//            '//div[contains(@class,\'SubmitButton-IconContainer\')]'
//        );
//
//        foreach ($quantityIncrementElements as $quantityIncrementElement) {
//            $quantityIncrementElement->click();
//            return;
//        }
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
//
//    public function featureName()
//    {
//        if (!array_key_exists('name', self::$featureData)) {
//            self::$featureData['name'] = 'Tester_' . time();
//        }
//
//        return self::$featureData['name'];
//    }

//    public function featurePhoneNumber()
//    {
//        if (!array_key_exists('phone_number', self::$featureData)) {
//            self::$featureData['phone_number'] = '07' . time();
//        }
//
//        return self::$featureData['phone_number'];
//    }
//
//
//    public function featureStreet()
//    {
//        if (!array_key_exists('street', self::$featureData)) {
//            self::$featureData['street'] = 'street_' . time();
//        }
//
//        return self::$featureData['street'];
//    }

//    public function featureAddress()
//    {
//        if (!array_key_exists('address', self::$featureData)) {
//            self::$featureData['address'] = 'address_' . time();
//        }
//
//        return self::$featureData['address'];
//    }
//
//    public function featurePostcode()
//    {
//        if (!array_key_exists('postcode', self::$featureData)) {
//            self::$featureData['postcode'] = 'BS9 2RD';
//        }
//
//        return self::$featureData['postcode'];
//    }

//    public function featureCity()
//    {
//        if (!array_key_exists('city', self::$featureData)) {
//            self::$featureData['city'] = 'city_' . time();
//        }
//
//        return self::$featureData['city'];
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

//    /**
//     * @Given /^I fill in the stripe checkout form$/
//     */
//    public function iFillInTheStripeCheckoutForm()
//    {
//        $fields = [
//            'email' => $this->featureEmail(),
//            'billingName' => $this->featureName(),
//            'Postal code' => $this->featurePostcode(),
//            'cardNumber' => '4242424242424242',
//            'MM / YY' => '02 / 22',
//            'CVC' => '222'
//        ];
//
//
//        $fieldsToSetNextLoop = $fields;
//
//        for ($i=0; $i<10; $i++) {
//            $fieldsToSet = $fieldsToSetNextLoop;
//            $fieldsToSetNextLoop = [];
//
//            foreach ($fieldsToSet as $fieldIdentifier => $value) {
//                $inputField = $this->getSession()->getPage()->findField($fieldIdentifier);
//                if ($inputField === null) {
//                    sleep(1);
//                    $fieldsToSetNextLoop[$fieldIdentifier] = $value;
//                }
//                else {
//                    $inputField->setValue($value);
//                }
//            }
//
//            if (count($fieldsToSetNextLoop) === 0) {
//                break;
//            }
//            sleep(1);
//        }
//
//        if (count($fieldsToSetNextLoop) !== 0) {
//            $message = sprintf(
//                "Failed to set all fields. Remaining [%s]",
//                implode(", ", $fieldsToSetNextLoop)
//            );
//            throw new \Exception($message);
//        }
//    }

//    /**
//     * @Given /^I set all the quantities to (-?\d+)$/
//     */
//    public function iSetAllTheQuantitiesTo($arg1)
//    {
//        $quantityElements = $this->findQuantityElements();
//        foreach ($quantityElements as $quantityElement) {
//            $quantityElement->setValue($arg1);
//        }
//    }

//    /**
//     * @Then /^All the quantities should be (\d+)$/
//     */
//    public function allTheQuantitiesShouldBe($arg1)
//    {
//        $expectedValue = (int)$arg1;
//
//        $quantityElements = $this->findQuantityElements();
//
//        foreach ($quantityElements as $quantityElement) {
//            $value = (int)$quantityElement->getValue();
//
//            if ($value !== $expectedValue) {
//                $message = sprintf(
//                    "Quantity element %s is not value [%s] but instead[%s]",
//                    $quantityElement->getAttribute('id'),
//                    $expectedValue,
//                    $value
//                );
//                throw new \Exception($message);
//            }
//        }
//    }

//    /**
//     * @Given /^I increment all the quantities$/
//     */
//    public function iIncrementAllTheQuantities()
//    {
//        $quantityIncrementElements = $this->findAll('xpath', '//span[contains(@class,\'osf_sku_quantity_inc\')]');
//
//        foreach ($quantityIncrementElements as $quantityIncrementElement) {
//            $quantityIncrementElement->click();
//        }
//    }
//
//    /**
//     * @Given /^I decrement all the quantities$/
//     */
//    public function iDecrementAllTheQuantities()
//    {
//        $quantityIncrementElements = $this->findAll('xpath', '//span[contains(@class,\'osf_sku_quantity_dec\')]');
//
//        foreach ($quantityIncrementElements as $quantityIncrementElement) {
//            $quantityIncrementElement->click();
//        }
//    }

//    private function findQuantityElements()
//    {
//        return $this->findAll('xpath', '//input[contains(@class,\'sku_quantity\')]');
//    }
//
//    /**
//     * @Given /^a payment should have been made for this project$/
//     */
//    public function aPaymentShouldHaveBeenMadeForThisProject()
//    {
//        throw new PendingException();
//    }


    /**
     * @Then /^debug what happens next$/
     */
    public function debugWhatHappensNext(): void
    {
        throw new PendingException("Need to define what happens here properly.");
    }

//    /**
//     * @Given /^the payment should have been recorded$/
//     */
//    public function thePaymentShouldHaveBeenRecorded()
//    {
//        $injector = createInjector();
//        $checkoutSessionRepo = $injector->make(DatabaseStripeCheckoutSessionRepo::class);
//
//        $sessions = $checkoutSessionRepo->getSessionsSince(self::$start_time);
//        if (count($sessions) === 0) {
//            throw new \Exception("No StripeCheckoutSession created");
//        }
//        if (count($sessions) === 0) {
//            throw new \Exception("More than one stripeCheckoutSession created - this is confusing");
//        }
//
//        $checkoutSession = $sessions[0];
//
//        /** @var \Osf\Model\StripeCheckoutSession $checkoutSession */
//        if ($checkoutSession->getStatus() !== StripeCheckoutSession::STATUS_COMPLETE) {
//            throw new \Exception("checkout session found, but it is not completed.");
//        }
//    }

//    /**
//     * @Then /^a purchase order should have been raised\.$/
//     */
//    public function aPurchaseOrderShouldHaveBeenRaised()
//    {
//        throw new PendingException();
//    }

    /**
     * @Then /^I should be on the page "([^"]*)" within (\d+) seconds$/
     */
    public function iShouldBeOnThePageWithinSeconds(string $expectedPath, int $seconds): void
    {
        $startTime = microtime(true);

        do {
            $currentUrl = $this->getSession()->getCurrentUrl();
            $path = parse_url($currentUrl, PHP_URL_PATH);
            if (strcasecmp($path, $expectedPath) === 0) {
                return;
            }

            usleep(100 * 1000);//tenth of a second

            $currentTime = microtime(true);
            $timeElapsed = $currentTime - $startTime;
            if ($timeElapsed > $seconds) {
                throw new \Exception("Didn't reach domain [$expectedPath] within [$seconds] seconds.");
            }
        }
        while (1);
    }


    /**
     * @Then /^I should see "([^"]*)" on the page$/
     */
    public function i_should_see_text_on_the_page(string $text): void
    {
        $this->assertSession()->pageTextContains($this->fixStepArgument($text));
    }

    /**
     * @Then /^take a screenshot$/
     */
    public function takeAScreenshot(): void
    {
        $this->takeDebugScreenshot();
        echo "screenshot...\n";
    }
}
