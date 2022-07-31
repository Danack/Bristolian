<?php

declare(strict_types = 1);

use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;
use Behat\Mink\Element\NodeElement;

function takeScreenShot(ChromeDriver $driver)
{
    static $counter = 0;

    $screenshot = $driver->getScreenshot();


    $metrics = $driver->page->send('Page.getLayoutMetrics');
    $weight = ceil($metrics['contentSize']['width']);
    $height = ceil($metrics['contentSize']['height']);
    $this->setVisibleSize($weight, $height);
    $screenshot = $this->page->send(
        'Page.captureScreenshot',
        ['clip' =>  ['x' => 0, 'y' => 0, 'width' => $weight, 'height' => $height, 'scale' => 1]]
    );
    $screenshot = base64_decode($screenshot['data']);




    $filename = "test_output_$counter.png";
    file_put_contents($filename, $screenshot);

    $counter += 1;
}

function findByXpath(ChromeDriver $driver, $selector): ?NodeElement
{
    echo "Looking for $selector \n";

    try {
        $elements = $driver->find($selector);
    }
    catch (Throwable $te) {
        echo "well, something went wrong.\n";
        echo $te->getMessage();
        exit(-1);
    }


    if ($elements !== null) {

        if (is_array($elements) !== true) {
            throw new \Exception("Finding selector [$selector] on page, failed. Didn't get an array back");
        }

        if (count($elements) === 0) {
            echo "Found no elements for $selector ...\n";
            return null;
        }

        $element = $elements[0];

        if (!($element instanceof NodeElement)) {
            takeScreenShot($driver);
            $message = "Finding selector [$selector] on page, failed. Didn't get a NodeElement back";
            $message .= " instead have a " . gettype($element);
            throw new \Exception($message);
        }

        return $element;
    }

    return null;
}




function findAndClickButton(ChromeDriver$driver, string $text)
{
    $next_button_element = findByXpath($driver, "//*[text() = '$text']");

    if ($next_button_element === null) {
        echo "Failed to find the '$text' button.\n";
        exit(-1);
    }

    echo "Maybe found the '$text' button?\n";

    try {
        $next_button_element->click();
        echo "Button is clicked\n.";
    }
    catch (Throwable $te) {
        echo "well, something went wrong clicking a button.\n";
        echo $te->getMessage();
        exit(-1);
    }
}


function findAndClickButtonIfAvailable(ChromeDriver$driver, string $text): bool
{
    $button_element = findByXpath($driver, "//*[text() = '$text']");

    if ($button_element === null) {
        return false;
    }

    echo "Maybe found the '$text' button?\n";

    $button_element->click();
    echo "And it should be clicked.\n";
    return true;
}
