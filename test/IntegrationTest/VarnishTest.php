<?php

declare(strict_types = 1);

namespace IntegrationTest;

use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @coversNothing
 */
#[Group('needs_fixing')]
class VarnishTest extends BaseTestCase
{
    public static function providesCountryLookupWorks()
    {
        return [
            ['85.214.132.117', 'DE'],
            ['5.45.192.1', 'RU'],   // Yandex
            ['163.177.112.32', 'CN'],
            ['86.7.192.139', 'GB'],
        ];
    }

    #[DataProvider('providesCountryLookupWorks')]
    public function testCountryLookupWorks(string $ipAddressToTest, string $expectedCountry)
    {
        $data = fetchDataWithHeaders(
            'http://local.app.opensourcefees.com/debug/headers',
            ['X-IP-Spoof: ' . $ipAddressToTest]
        );

        $this->assertCount(1, $data['HTTP_X_IP_TO_USE']);
        $ipBeingUsed = $data['HTTP_X_IP_TO_USE'][0];
        $this->assertEquals($ipAddressToTest, $ipBeingUsed);

        $this->assertCount(1, $data['HTTP_X_COUNTRY_CODE']);
        $countryCodeFound = $data['HTTP_X_COUNTRY_CODE'][0];
        $this->assertEquals($expectedCountry, $countryCodeFound);
    }

    public static function providesCountryLookupBlank()
    {
        return [
            ['0.0.0.0'],
            ['192.168.0.1'],
            ['10.0.0.0']
        ];
    }

    #[DataProvider('providesCountryLookupBlank')]
    public function testCountryLookupBlank(string $ipAddressToTest)
    {
        $data = fetchDataWithHeaders(
            'http://local.app.opensourcefees.com/debug/headers',
            ['X-IP-Spoof: ' . $ipAddressToTest]
        );

        $this->assertCount(1, $data['HTTP_X_IP_TO_USE']);
        $ipBeingUsed = $data['HTTP_X_IP_TO_USE'][0];
        $this->assertEquals($ipAddressToTest, $ipBeingUsed);

        $this->assertCount(1, $data['HTTP_X_COUNTRY_CODE']);
        $countryCodeFound = $data['HTTP_X_COUNTRY_CODE'][0];
        $this->assertEquals("", $countryCodeFound);
    }
}
