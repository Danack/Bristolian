<?php

declare(strict_types = 1);

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/scrape_functions.php";


use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;


$options = [
    'socketTimeout' => 30,
    'domWaitTimeout' => 3000,
];

$driver = new ChromeDriver(
    'http://10.254.254.254:9222',
    null,
    'http://10.254.254.254:9222/',
    $options
);



$session = new Session($driver);



$password = 'vs*33by2yy/kxB6z(TQ/`8\n';

$session->visit("https://twitter.com/i/flow/login");
$session->resizeWindow(800, 800);

// "--window-size=1920,1200"

sleep(2);

for ($i=0; $i < 30; $i += 1) {
    takeScreenShot($driver);
    $username_element = findByXpath($driver, "//input[contains(@autocomplete, 'username')]");

    if ($username_element !== null) {
        break;
    }

    sleep(2);
}

if ($username_element === null) {
    echo "Failed to find username input field.";
    exit(-1);
}

echo "Allegedly found input field.\n";

$username_element->setValue('BristolianOrg');

sleep(2);
takeScreenShot($driver);

findAndClickButton($driver, "Next");

sleep(4);

for ($i=0; $i < 30; $i += 1) {
    takeScreenShot($driver);
    $password_element = findByXpath($driver, "//input[contains(@name, 'password')]");

    if ($password_element !== null) {
        break;
    }

    sleep(2);
}

if ($password_element === null) {
    echo "Failed to find the password field.\n";
    exit(-1);
}

$password_element->setValue($password);

sleep(2);

findAndClickButton($driver, "Log in");

sleep(5);

echo "Are we logged in?\n";
takeScreenShot($driver);


$clicked = findAndClickButtonIfAvailable($driver, "Accept all cookies");
sleep(2);
takeScreenShot($driver);

echo "fin.";