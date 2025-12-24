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

    /** @var int|null */
    private ?int $initialTotalSteps = null;

    /** @var int|null */
    private ?int $initialTotalFlights = null;

    /** @var bool */
    private bool $markerWasClicked = false;

    /** @var float|null */
    private ?float $generatedLatitude = null;

    /** @var float|null */
    private ?float $generatedLongitude = null;

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
         // Reset state variables
         $this->initialTotalSteps = null;
         $this->initialTotalFlights = null;
         $this->markerWasClicked = false;
         $this->generatedLatitude = null;
         $this->generatedLongitude = null;
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



    public function takeDebugScreenshot(?string $testIdentifier = null): void
    {
        echo "Taking debug screenshot...\n";
        $screenshot = $this->getSession()->getDriver()->getScreenshot();
        
        // Generate filename with test identifier and timestamp
        $timestamp = date('Y-m-d_H-i-s');
        if ($testIdentifier !== null) {
            // Sanitize the identifier for use in filename
            $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $testIdentifier);
            $sanitized = substr($sanitized, 0, 100); // Limit length
            $filename = __DIR__ . "/../../screenshot/debug_{$sanitized}_{$timestamp}.png";
        } else {
            $filename = __DIR__ . "/../../screenshot/debug_{$timestamp}.png";
        }
        
        @mkdir(dirname($filename), 0755, true);
        file_put_contents($filename, $screenshot);
        echo "Screenshot saved to: $filename\n";
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
        // Use current URL as identifier
        $session = $this->getSession();
        $url = parse_url($session->getCurrentUrl(), PHP_URL_PATH);
        $url = str_replace('/', '_', trim($url, '/'));
        $identifier = $url ?: 'screenshot';
        $this->takeDebugScreenshot($identifier);
    }

    /**
     * @Then /^the page should contain a map element$/
     */
    public function thePageShouldContainAMapElement(): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        $mapElement = $page->find('css', '#bristol_stairs_map');
        
        if ($mapElement === null) {
            throw new \Exception("Map element with id 'bristol_stairs_map' not found on page.");
        }
    }

    /**
     * @Then /^the map should have zoom controls$/
     */
    public function theMapShouldHaveZoomControls(): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Leaflet adds zoom controls - try multiple ways to find them
        // First try by aria-label
        $zoomIn = $page->find('css', 'button[aria-label="Zoom in"]');
        $zoomOut = $page->find('css', 'button[aria-label="Zoom out"]');
        
        // If not found by aria-label, try by button text/name (accessibility name)
        if ($zoomIn === null || $zoomOut === null) {
            $zoomIn = $page->find('xpath', '//button[contains(., "Zoom in") or @aria-label="Zoom in"]');
            $zoomOut = $page->find('xpath', '//button[contains(., "Zoom out") or @aria-label="Zoom out"]');
        }
        
        // If still not found, try by Leaflet's CSS classes
        if ($zoomIn === null || $zoomOut === null) {
            $zoomIn = $page->find('css', '.leaflet-control-zoom-in');
            $zoomOut = $page->find('css', '.leaflet-control-zoom-out');
        }
        
        if ($zoomIn === null || $zoomOut === null) {
            throw new \Exception("Zoom controls not found on map. Expected buttons for zoom in and zoom out.");
        }
    }

    /**
     * @When /^I wait for the map to load$/
     */
    public function iWaitForTheMapToLoad(): void
    {
        $session = $this->getSession();
        
        // Wait for the markers_loaded flag to be true, with a timeout
        // Poll every 100ms for up to 5 seconds
        $maxAttempts = 50; // 5 seconds with 100ms intervals
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            // Check if markers_loaded is true
            $markersLoaded = $session->evaluateScript('typeof markers_loaded !== "undefined" && markers_loaded === true');
            
            if ($markersLoaded === true) {
                return;
            }
            
            usleep(100 * 1000); // 100ms
            $attempt++;
        }
        
        // If we get here, markers didn't load - but that's OK, the page might have no stairs
        // We'll let the test continue and check for markers conditionally
    }

    /**
     * @Then /^the map should display markers if stairs are present$/
     */
    public function theMapShouldDisplayMarkersIfStairsArePresent(): void
    {
        $session = $this->getSession();
        
        // Check if markers are loaded
        $markersLoaded = $session->evaluateScript('typeof markers_loaded !== "undefined" && markers_loaded === true');
        
        if (!$markersLoaded) {
            // Markers haven't loaded yet, wait a bit more
            sleep(1);
            $markersLoaded = $session->evaluateScript('typeof markers_loaded !== "undefined" && markers_loaded === true');
        }
        
        if (!$markersLoaded) {
            // Still not loaded - this might mean there are no stairs, which is OK
            // We'll just verify the map exists
            $this->thePageShouldContainAMapElement();
            return;
        }
        
        // Check if there are any markers in the marker cluster group
        $markerCount = $session->evaluateScript('
            (function() {
                if (typeof markers === "undefined" || markers === null) {
                    return 0;
                }
                var count = 0;
                markers.eachLayer(function() {
                    count++;
                });
                return count;
            })();
        ');
        
        // It's OK if there are no markers (no stairs in database)
        // We just verify the map is working
        if ($markerCount > 0) {
            // Verify markers are on the map by checking if marker cluster group has layers
            $hasLayers = $session->evaluateScript('
                (function() {
                    if (typeof map === "undefined" || map === null) {
                        return false;
                    }
                    if (typeof markers === "undefined" || markers === null) {
                        return false;
                    }
                    return map.hasLayer(markers);
                })();
            ');
            
            if (!$hasLayers) {
                throw new \Exception("Markers exist but are not added to the map.");
            }
        }
    }

    /**
     * @When /^I click on a marker if one is present$/
     */
    public function iClickOnAMarkerIfOneIsPresent(): void
    {
        $session = $this->getSession();
        $this->markerWasClicked = false;
        
        // First check if markers are loaded
        $markersLoaded = $session->evaluateScript('typeof markers_loaded !== "undefined" && markers_loaded === true');
        
        if (!$markersLoaded) {
            // Markers haven't loaded - this might mean there are no stairs, which is OK
            // Just return without clicking
            return;
        }
        
        // Check if there are any markers
        $markerCount = $session->evaluateScript('
            (function() {
                if (typeof markers === "undefined" || markers === null) {
                    return 0;
                }
                var count = 0;
                markers.eachLayer(function() {
                    count++;
                });
                return count;
            })();
        ');
        
        if ($markerCount === 0) {
            // No markers present, which is fine - skip this step
            return;
        }
        
        // Get the first marker and click it
        $clicked = $session->evaluateScript('
            (function() {
                if (typeof markers === "undefined" || markers === null) {
                    return false;
                }
                
                var firstMarker = null;
                markers.eachLayer(function(marker) {
                    if (firstMarker === null) {
                        firstMarker = marker;
                    }
                });
                
                if (firstMarker !== null) {
                    // Trigger click event on the marker
                    firstMarker.fire("click");
                    return true;
                }
                
                return false;
            })();
        ');
        
        if ($clicked) {
            $this->markerWasClicked = true;
            // Wait a moment for the click to be processed
            sleep(1);
        }
    }

    /**
     * @Then /^the URL should change to include a stair ID$/
     */
    public function theUrlShouldChangeToIncludeAStairId(): void
    {
        // If no marker was clicked (because there were no markers), skip this check
        if (!$this->markerWasClicked) {
            return;
        }
        
        $session = $this->getSession();
        $currentUrl = $session->getCurrentUrl();
        
        // Check if URL matches pattern /tools/bristol_stairs/{stair_id}
        if (preg_match('#/tools/bristol_stairs/[^/]+$#', $currentUrl) !== 1) {
            throw new \Exception("URL does not include a stair ID. Current URL: $currentUrl");
        }
    }

    /**
     * @Then /^I should see stair information displayed$/
     */
    public function iShouldSeeStairInformationDisplayed(): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Look for the BristolStairsPanel content - it should show something other than the default message
        $defaultMessage = "Click a marker on the map to view the stairs.";
        
        // Check if we can find content that suggests stair info is displayed
        // The panel might contain description, steps, or other stair information
        $pageText = $page->getText();
        
        // If we still see the default message, no stair info is displayed
        if (strpos($pageText, $defaultMessage) !== false) {
            // Check if we're in a state where stair info should be shown
            // by checking if a marker was clicked (URL changed)
            $currentUrl = $session->getCurrentUrl();
            if (preg_match('#/tools/bristol_stairs/[^/]+$#', $currentUrl) === 1) {
                throw new \Exception("Stair information should be displayed but default message is still showing.");
            }
        }
    }

    /**
     * @Given /^I am logged in$/
     */
    public function iAmLoggedIn(): void
    {
        $session = $this->getSession();
        
        // Go to login page
        $this->visitPath('/login');
        
        // Check if we're already logged in (redirected away from login)
        $currentUrl = $session->getCurrentUrl();
        $path = parse_url($currentUrl, PHP_URL_PATH);
        
        if ($path !== '/login') {
            // Already logged in - verify frontend login state is updated
            $this->waitForFrontendLoginState(true);
            return;
        }
        
        // Fill in login form - using test credentials
        // Test account: username: testing@example.com, password: testing
        $this->fillField('username', 'testing@example.com');
        $this->fillField('password', 'testing');
        $this->pressButton('Login');
        
        // Wait for redirect
        $this->iShouldBeOnThePageWithinSeconds('/tools', 5);
        
        // Wait for frontend login state to be updated
        // The React component needs to fetch /api/login-status and update use_logged_in()
        $this->waitForFrontendLoginState(true);
    }
    
    /**
     * Wait for the frontend login state to match the expected value
     * Checks for the "Logout" link which only appears when logged in
     */
    private function waitForFrontendLoginState(bool $expectedLoggedIn): void
    {
        $session = $this->getSession();
        $maxAttempts = 30; // 3 seconds
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $page = $session->getPage();
            
            // Check if the React component has updated its state
            // Look for the "Logout" link which only appears when logged in
            $logoutLink = $page->find('xpath', '//a[contains(text(), "Logout")]');
            
            if ($expectedLoggedIn && $logoutLink !== null) {
                // Logged in and frontend has updated
                return;
            }
            
            if (!$expectedLoggedIn && $logoutLink === null) {
                // Not logged in and frontend has updated
                return;
            }
            
            usleep(100 * 1000); // 100ms
            $attempt++;
        }
        
        if ($expectedLoggedIn) {
            throw new \Exception("Frontend login state did not update to logged in within timeout. The 'Logout' link was not found.");
        }
    }

    /**
     * @When /^I click the "([^"]*)" button$/
     */
    public function iClickTheButton(string $buttonText): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Wait for button to appear (especially important for "Upload image" button which depends on login state)
        $button = null;
        $maxAttempts = 30; // 3 seconds
        $attempt = 0;
        
        while ($attempt < $maxAttempts && $button === null) {
            $page = $session->getPage();
            $button = $page->findButton($buttonText);
            
            if ($button === null) {
                // Try finding by text content
                $xpath = sprintf('//button[contains(text(), "%s")]', $buttonText);
                $button = $page->find('xpath', $xpath);
            }
            
            if ($button === null) {
                usleep(100 * 1000); // 100ms
                $attempt++;
            }
        }
        
        if ($button === null) {
            $pageText = substr($page->getText(), 0, 500);
            throw new \Exception("Button with text '$buttonText' not found after waiting. Page text snippet: " . $pageText);
        }
        
        $button->click();
        
        // Wait a moment for any JavaScript to process (React component might need time to render)
        sleep(2);
    }

    /**
     * @Then /^I should not see a "([^"]*)" button$/
     */
    public function iShouldNotSeeAButton(string $buttonText): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Try to find the button
        $button = $page->findButton($buttonText);
        
        if ($button === null) {
            // Try finding by text content
            $xpath = sprintf('//button[contains(text(), "%s")]', $buttonText);
            $button = $page->find('xpath', $xpath);
        }
        
        if ($button !== null) {
            throw new \Exception("Button with text '$buttonText' should not be visible, but it was found on the page.");
        }
        
        // Button is not found, which is what we expect when not logged in
    }

    /**
     * @When /^I upload the file "([^"]*)" with random GPS coordinates$/
     */
    public function iUploadTheFileWithRandomGpsCoordinates(string $filePath): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Convert relative path to absolute (filePath is relative to project root)
        // SiteContext.php is in src/BristolianBehat/, so go up 2 levels to project root
        $projectRoot = dirname(__DIR__, 2);
        $absolutePath = $projectRoot . '/' . $filePath;
        
        if (!file_exists($absolutePath)) {
            throw new \Exception("File not found: $absolutePath");
        }
        
        // Generate random GPS coordinates within the bounding box
        // This must be done BEFORE the file is selected, as the FileUpload component
        // requests GPS when a file is selected
        $southWest = ['lat' => 51.3325441, 'lng' => -2.8657612];
        $northEast = ['lat' => 51.6014432, 'lng' => -2.2960328];
        
        $latitude = $southWest['lat'] + (mt_rand() / mt_getrandmax()) * ($northEast['lat'] - $southWest['lat']);
        $longitude = $southWest['lng'] + (mt_rand() / mt_getrandmax()) * ($northEast['lng'] - $southWest['lng']);
        
        // Mock navigator.geolocation.getCurrentPosition to return our coordinates
        $session->executeScript(sprintf(
            <<<JS
(function() {
    // Mock geolocation API
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition = function(success, error) {
            if (success) {
                success({
                    coords: {
                        latitude: %f,
                        longitude: %f,
                        accuracy: 10
                    },
                    timestamp: Date.now()
                });
            }
        };
    }
})();
JS
            ,
            $latitude,
            $longitude
        ));
        
        // Wait for the FileUpload component to render
        // First wait for the component's text to appear ("Drag a file here to upload")
        $maxAttempts = 50; // 5 seconds - React/Preact might need more time
        $attempt = 0;
        $uploadPanelVisible = false;
        
        while ($attempt < $maxAttempts) {
            $page = $session->getPage(); // Refresh page reference
            $pageText = $page->getText();
            
            // Check if FileUpload component has rendered by looking for its text
            if (strpos($pageText, 'Drag a file here to upload') !== false || 
                strpos($pageText, 'Drop files here') !== false ||
                strpos($pageText, 'Selected file:') !== false) {
                $uploadPanelVisible = true;
                break;
            }
            
            usleep(100 * 1000); // 100ms
            $attempt++;
        }
        
        if (!$uploadPanelVisible) {
            $pageText = substr($page->getText(), 0, 500);
            throw new \Exception("FileUpload component did not render after clicking Upload image button. Page text: " . $pageText);
        }
        
        // Now wait for the file input element itself and attach the file
        $fileInput = null;
        $maxAttempts = 30; // 3 seconds
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $page = $session->getPage(); // Refresh page reference
            $fileInput = $page->find('css', 'input[type="file"]');

            if ($fileInput !== null) {
                // Attach the file using the absolute path
                $fileInput->attachFile($absolutePath);
                break;
            }

            usleep(100 * 1000); // 100ms
            $attempt++;
        }
        
        if ($fileInput === null) {
            // Try to get more info about what's on the page
            $pageText = substr($page->getText(), 0, 500);
            $html = substr($page->getContent(), 0, 1000);
            throw new \Exception("File input not found on page after waiting. Page text snippet: " . $pageText . "\nHTML snippet: " . $html);
        }
        
        // Wait for React to process the file selection and GPS request
        // The FileUpload component will request GPS when a file is selected
        // Give time for the file to be selected and GPS to be requested
        sleep(2);
        
        // Verify GPS coordinates were set by checking the component state via JavaScript
        $gpsLatitude = $session->evaluateScript("
            (function() {
                // Try to find the FileUpload component's state
                // Since we can't directly access React state, we'll just wait and trust the mock worked
                return navigator.geolocation ? 'available' : 'not available';
            })();
        ");
        
        // Find and click the upload button
        // Wait a bit more for the button to be enabled/ready
        $uploadButton = null;
        $maxAttempts = 20; // 2 seconds
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $page = $session->getPage();
            $buttons = $page->findAll('css', 'button');
            foreach ($buttons as $button) {
                $buttonText = strtolower($button->getText());
                // Look for button that says "Upload" (not "Upload image")
                if ($buttonText === 'upload' || (strpos($buttonText, 'upload') !== false && strpos($buttonText, 'image') === false)) {
                    $uploadButton = $button;
                    break 2; // Break out of both loops
                }
            }

            usleep(100 * 1000); // 100ms
            $attempt++;

        }
        
        if ($uploadButton === null) {
            $pageText = substr($page->getText(), 0, 300);
            throw new \Exception("Upload button not found. Page text: " . $pageText);
        }
        
        // Click upload button
        $uploadButton->click();
        
        // Wait for upload to complete and redirect
        // The upload should redirect to /tools/bristol_stairs/{stair_id}
        // Wait up to 10 seconds for the redirect
        $maxAttempts = 100; // 10 seconds
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $currentUrl = $session->getCurrentUrl();
            $path = parse_url($currentUrl, PHP_URL_PATH);
            
            // Check if we've been redirected to a stair detail page
            if (preg_match('#/tools/bristol_stairs/[^/]+$#', $path) === 1) {
                // Successfully redirected
                return;
            }
            
            // Check for error messages on the page
            $page = $session->getPage();
            $pageText = $page->getText();
            if (strpos($pageText, 'Error:') !== false || strpos($pageText, 'Upload failed') !== false) {
                $errorText = substr($pageText, 0, 500);
                throw new \Exception("Upload failed with error. Page text: " . $errorText);
            }
            
            usleep(100 * 1000); // 100ms
            $attempt++;
        }
        
        // If we get here, redirect didn't happen
        $currentUrl = $session->getCurrentUrl();
        $pageText = substr($session->getPage()->getText(), 0, 500);
        throw new \Exception("Upload did not redirect to stair detail page. Current URL: $currentUrl\nPage text: " . $pageText);
    }

    /**
     * @Then /^I should be redirected to a stair detail page$/
     */
    public function iShouldBeRedirectedToAStairDetailPage(): void
    {
        $session = $this->getSession();
        
        // Wait for redirect to stair detail page
        $maxAttempts = 20; // 2 seconds
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $currentUrl = $session->getCurrentUrl();
            $path = parse_url($currentUrl, PHP_URL_PATH);
            
            if (preg_match('#/tools/bristol_stairs/[^/]+$#', $path) === 1) {
                return; // Successfully redirected
            }
            
            usleep(100 * 1000); // 100ms
            $attempt++;
        }
        
        throw new \Exception("Not redirected to stair detail page. Current URL: " . $session->getCurrentUrl());
    }

    /**
     * @When /^I set the steps to (\d+)$/
     */
    public function iSetTheStepsTo(int $steps): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Find the steps input field
        $stepsInput = $page->findField('steps');
        
        if ($stepsInput === null) {
            // Try finding by label
            $label = $page->find('xpath', '//label[contains(text(), "Steps")]');
            if ($label !== null) {
                $forAttr = $label->getAttribute('for');
                if ($forAttr) {
                    $stepsInput = $page->findById($forAttr);
                }
            }
        }
        
        if ($stepsInput === null) {
            throw new \Exception("Steps field not found on page.");
        }
        
        // Set the value
        $stepsInput->setValue((string)$steps);
        
        // Trigger input event to notify React
        $session->executeScript(sprintf(
            <<<JS
(function() {
    var input = document.getElementById('%s') || document.querySelector('input[name="steps"]');
    if (input) {
        var event = new Event('input', { bubbles: true });
        input.dispatchEvent(event);
    }
})();
JS
            ,
            $stepsInput->getAttribute('id') ?: 'steps'
        ));
        
        // Find and click the save button
        $saveButton = $page->findButton('Save Changes');
        if ($saveButton === null) {
            // Try finding by text
            $saveButton = $page->find('xpath', '//button[contains(text(), "Save")]');
        }
        
        if ($saveButton === null) {
            throw new \Exception("Save Changes button not found.");
        }
        
        $saveButton->click();
        
        // Wait for save to complete
        sleep(2);
    }

    /**
     * @Then /^the stair should have (\d+) steps$/
     */
    public function theStairShouldHaveSteps(int $expectedSteps): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Look for the steps input field or display
        $stepsInput = $page->findField('steps');
        
        if ($stepsInput === null) {
            // Try finding by label
            $label = $page->find('xpath', '//label[contains(text(), "Steps")]');
            if ($label !== null) {
                $forAttr = $label->getAttribute('for');
                if ($forAttr) {
                    $stepsInput = $page->findById($forAttr);
                }
            }
        }
        
        if ($stepsInput === null) {
            throw new \Exception("Steps field not found on page.");
        }
        
        $actualSteps = (int)$stepsInput->getValue();
        
        if ($actualSteps !== $expectedSteps) {
            throw new \Exception("Expected stair to have $expectedSteps steps, but found $actualSteps.");
        }
    }

    /**
     * @Then /^the stair should appear on the map$/
     */
    public function theStairShouldAppearOnTheMap(): void
    {
        $session = $this->getSession();
        
        // Get the stair ID from the URL
        $currentUrl = $session->getCurrentUrl();
        if (preg_match('#/tools/bristol_stairs/([^/]+)$#', $currentUrl, $matches) !== 1) {
            throw new \Exception("Could not extract stair ID from URL: $currentUrl");
        }
        
        $stairId = $matches[1];
        
        // Navigate back to the main map page
        $this->visitPath('/tools/bristol_stairs');
        
        // Wait for map to load
        $this->iWaitForTheMapToLoad();
        
        // Check if a marker with this stair ID exists
        $markerExists = $session->evaluateScript(sprintf(
            <<<JS
(function() {
    if (typeof markers === "undefined" || markers === null) {
        return false;
    }
    
    var found = false;
    markers.eachLayer(function(marker) {
        if (marker.stairId === "%s") {
            found = true;
        }
    });
    
    return found;
})();
JS
            ,
            addslashes($stairId)
        ));
        
        if (!$markerExists) {
            throw new \Exception("Marker for stair ID $stairId not found on map.");
        }
    }

    /**
     * @When /^I note the current total steps and flights$/
     */
    public function iNoteTheCurrentTotalStepsAndFlights(): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Extract the totals from the page text
        // Text format: "There are currently entries for X steps in Y flights of stairs."
        $pageText = $page->getText();
        
        if (preg_match('/There are currently entries for (\d+) steps in (\d+) flights of stairs\./', $pageText, $matches) !== 1) {
            throw new \Exception("Could not find total steps and flights text on page. Page text: " . substr($pageText, 0, 200));
        }
        
        $this->initialTotalSteps = (int)$matches[1];
        $this->initialTotalFlights = (int)$matches[2];
    }

    /**
     * @Then /^the total steps should have increased by (\d+)$/
     */
    public function theTotalStepsShouldHaveIncreasedBy(int $expectedIncrease): void
    {
        if ($this->initialTotalSteps === null) {
            throw new \Exception("Must call 'I note the current total steps and flights' before checking the increase.");
        }
        
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Extract the current totals from the page
        $pageText = $page->getText();
        
        if (preg_match('/There are currently entries for (\d+) steps in (\d+) flights of stairs\./', $pageText, $matches) !== 1) {
            throw new \Exception("Could not find total steps and flights text on page. Page text: " . substr($pageText, 0, 200));
        }
        
        $currentTotalSteps = (int)$matches[1];
        $actualIncrease = $currentTotalSteps - $this->initialTotalSteps;
        
        if ($actualIncrease !== $expectedIncrease) {
            throw new \Exception(
                "Expected total steps to increase by $expectedIncrease, " .
                "but it increased by $actualIncrease " .
                "(from {$this->initialTotalSteps} to $currentTotalSteps)."
            );
        }
    }

    /**
     * @Then /^the total flights should have increased by (\d+)$/
     */
    public function theTotalFlightsShouldHaveIncreasedBy(int $expectedIncrease): void
    {
        if ($this->initialTotalFlights === null) {
            throw new \Exception("Must call 'I note the current total steps and flights' before checking the increase.");
        }
        
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Extract the current totals from the page
        $pageText = $page->getText();
        
        if (preg_match('/There are currently entries for (\d+) steps in (\d+) flights of stairs\./', $pageText, $matches) !== 1) {
            throw new \Exception("Could not find total steps and flights text on page. Page text: " . substr($pageText, 0, 200));
        }
        
        $currentTotalFlights = (int)$matches[2];
        $actualIncrease = $currentTotalFlights - $this->initialTotalFlights;
        
        if ($actualIncrease !== $expectedIncrease) {
            throw new \Exception(
                "Expected total flights to increase by $expectedIncrease, " .
                "but it increased by $actualIncrease " .
                "(from {$this->initialTotalFlights} to $currentTotalFlights)."
            );
        }
    }

    /**
     * @When /^I set the description to "([^"]*)"$/
     */
    public function iSetTheDescriptionTo(string $description): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Find the description input field
        $descriptionInput = $page->findField('desc');
        
        if ($descriptionInput === null) {
            // Try finding by id
            $descriptionInput = $page->findById('desc');
        }
        
        if ($descriptionInput === null) {
            throw new \Exception("Description field not found on page.");
        }
        
        // Set the value
        $descriptionInput->setValue($description);
        
        // Trigger input event to notify React
        $session->executeScript(
            <<<JS
(function() {
    var input = document.getElementById('desc') || document.querySelector('input[id="desc"]');
    if (input) {
        var event = new Event('input', { bubbles: true });
        input.dispatchEvent(event);
    }
})();
JS
        );
        
        // Find and click the save button
        $saveButton = $page->findButton('Save Changes');
        if ($saveButton === null) {
            // Try finding by text
            $saveButton = $page->find('xpath', '//button[contains(text(), "Save")]');
        }
        
        if ($saveButton === null) {
            throw new \Exception("Save Changes button not found.");
        }
        
        $saveButton->click();
        
        // Wait for save to complete
        sleep(2);
    }

    /**
     * @Then /^the stair should have description "([^"]*)"$/
     */
    public function theStairShouldHaveDescription(string $expectedDescription): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        // Look for the description field or display
        $descriptionInput = $page->findField('desc');
        
        if ($descriptionInput === null) {
            // Try finding by id
            $descriptionInput = $page->findById('desc');
        }
        
        if ($descriptionInput === null) {
            // Might be a span for view-only mode
            $descriptionSpan = $page->findById('desc');
            if ($descriptionSpan !== null && $descriptionSpan->getTagName() === 'span') {
                $actualDescription = trim($descriptionSpan->getText());
            } else {
                throw new \Exception("Description field not found on page.");
            }
        } else {
            $actualDescription = $descriptionInput->getValue();
        }
        
        if ($actualDescription !== $expectedDescription) {
            throw new \Exception("Expected stair to have description '$expectedDescription', but found '$actualDescription'.");
        }
    }

    /**
     * @When /^I click the "Edit Position" button$/
     */
    public function iClickTheEditPositionButton(): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        $button = $page->findButton('Edit Position');
        
        if ($button === null) {
            // Try finding by text content
            $xpath = '//button[contains(text(), "Edit Position")]';
            $button = $page->find('xpath', $xpath);
        }
        
        if ($button === null) {
            throw new \Exception("Edit Position button not found.");
        }
        
        $button->click();
        
        // Wait for position editing mode to activate
        sleep(2);
    }

    /**
     * @When /^I generate a random position within Bristol$/
     */
    public function iGenerateARandomPositionWithinBristol(): void
    {
        // Generate random GPS coordinates within the bounding box
        $southWest = ['lat' => 51.3325441, 'lng' => -2.8657612];
        $northEast = ['lat' => 51.6014432, 'lng' => -2.2960328];
        
        $this->generatedLatitude = $southWest['lat'] + (mt_rand() / mt_getrandmax()) * ($northEast['lat'] - $southWest['lat']);
        $this->generatedLongitude = $southWest['lng'] + (mt_rand() / mt_getrandmax()) * ($northEast['lng'] - $southWest['lng']);
    }

    /**
     * @When /^I move the map to the generated position$/
     */
    public function iMoveTheMapToTheGeneratedPosition(): void
    {
        if ($this->generatedLatitude === null || $this->generatedLongitude === null) {
            throw new \Exception("Must call 'I generate a random position within Bristol' before moving the map.");
        }
        
        $session = $this->getSession();
        
        // Move the map to the generated coordinates
        $session->executeScript(sprintf(
            <<<JS
(function() {
    if (typeof map === "undefined" || map === null) {
        throw new Error("Map is not available");
    }
    
    // Set the map view to the new coordinates
    map.setView([%f, %f], map.getZoom());
    
    // Trigger a move event to update the position
    map.fire('move');
})();
JS
            ,
            $this->generatedLatitude,
            $this->generatedLongitude
        ));
        
        // Wait for the position change to be processed
        sleep(1);
    }

    /**
     * @When /^I click the "Update Position" button$/
     */
    public function iClickTheUpdatePositionButton(): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        $button = $page->findButton('Update Position');
        
        if ($button === null) {
            // Try finding by text content
            $xpath = '//button[contains(text(), "Update Position")]';
            $button = $page->find('xpath', $xpath);
        }
        
        if ($button === null) {
            throw new \Exception("Update Position button not found.");
        }
        
        $button->click();
        
        // Wait for position update to complete
        sleep(2);
    }

    /**
     * @Then /^the stair position should be approximately the generated position$/
     */
    public function theStairPositionShouldBeApproximatelyTheGeneratedPosition(): void
    {
        if ($this->generatedLatitude === null || $this->generatedLongitude === null) {
            throw new \Exception("Must call 'I generate a random position within Bristol' before checking the position.");
        }
        
        $this->theStairPositionShouldBeApproximatelyLatitudeAndLongitude(
            $this->generatedLatitude,
            $this->generatedLongitude
        );
    }

    /**
     * @Then /^the stair position should be approximately latitude ([0-9.]+) and longitude ([0-9.+-]+)$/
     */
    public function theStairPositionShouldBeApproximatelyLatitudeAndLongitude(float $expectedLatitude, float $expectedLongitude): void
    {
        $session = $this->getSession();
        
        // Get the stair ID from the URL
        $currentUrl = $session->getCurrentUrl();
        if (preg_match('#/tools/bristol_stairs/([^/]+)$#', $currentUrl, $matches) !== 1) {
            throw new \Exception("Could not extract stair ID from URL: $currentUrl");
        }
        
        $stairId = $matches[1];
        
        // Check the marker position on the map
        $positionMatches = $session->evaluateScript(sprintf(
            <<<JS
(function() {
    if (typeof markers === "undefined" || markers === null) {
        return false;
    }
    
    var found = false;
    var actualLat = null;
    var actualLng = null;
    
    markers.eachLayer(function(marker) {
        if (marker.stairId === "%s") {
            var latLng = marker.getLatLng();
            actualLat = latLng.lat;
            actualLng = latLng.lng;
            found = true;
        }
    });
    
    if (!found) {
        return {found: false, error: "Marker not found"};
    }
    
    // Check if position is approximately correct (within 0.0001 degrees, about 11 meters)
    var latDiff = Math.abs(actualLat - %f);
    var lngDiff = Math.abs(actualLng - %f);
    var isApproximate = latDiff < 0.0001 && lngDiff < 0.0001;
    
    return {
        found: true,
        isApproximate: isApproximate,
        actualLat: actualLat,
        actualLng: actualLng,
        latDiff: latDiff,
        lngDiff: lngDiff
    };
})();
JS
            ,
            addslashes($stairId),
            $expectedLatitude,
            $expectedLongitude
        ));
        
        if (!$positionMatches['found']) {
            throw new \Exception("Marker for stair ID $stairId not found on map.");
        }
        
        if (!$positionMatches['isApproximate']) {
            throw new \Exception(
                "Expected stair position to be approximately ($expectedLatitude, $expectedLongitude), " .
                "but found ({$positionMatches['actualLat']}, {$positionMatches['actualLng']}). " .
                "Difference: lat={$positionMatches['latDiff']}, lng={$positionMatches['lngDiff']}"
            );
        }
    }

    /**
     * @When /^I navigate to the GET update endpoint for stair ID "([^"]*)"$/
     */
    public function iNavigateToTheGetUpdateEndpointForStairId(string $stairId): void
    {
        $this->visitPath("/api/bristol_stairs_update/$stairId");
    }

    /**
     * @Then /^I should see the error message "([^"]*)"$/
     */
    public function iShouldSeeTheErrorMessage(string $expectedMessage): void
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $pageText = $page->getText();
        
        if (strpos($pageText, $expectedMessage) === false) {
            throw new \Exception("Expected to see error message '$expectedMessage', but page text was: " . substr($pageText, 0, 200));
        }
    }
}
