<?php

declare(strict_types = 1);

namespace BristolianTest;

/**
 * @coversNothing
 */
class FunctionsTest extends BaseTestCase
{
    /**
     * @covers ::getRandomId
     */
    public function testGetRandomId()
    {
        $id = getRandomId();
        $this->assertSame(64, strlen($id));
    }


    public function test_hackVarMap()
    {
    }

    /**
     * @covers ::formatLinesWithCount
     */
    public function test_formatLinesWithCount()
    {
        $result = formatLinesWithCount(['foo', 'bar']);
        $expected = <<<TEXT
#0 foo
#1 bar

TEXT;
        $this->assertSame($expected, $result);
    }

    public function test_continuallyExecuteCallable()
    {
    }

    /**
     * @covers json_decode_safe
     */
    public function test_json_decode_safe()
    {
        $data = ['foo' => 'bar'];
        $output = json_decode_safe(json_encode($data));

        $this->assertSame($data, $output);
    }

    public function test_json_encode_safe()
    {
    }

    public function test_getExceptionInfoAsArray()
    {
    }

    /**
     * @covers ::peak_memory
     */
    public function test_peak_memory()
    {
        $memory_string = peak_memory();
        $this->assertGreaterThanOrEqual(
            9,
            strlen($memory_string),
            "memory used is only " . $memory_string
        );
    }


    public function test_convertToValue()
    {
    }


    public function test_fetchUri()
    {
    }


    public function test_convertStringToHtmlResponse()
    {
    }


    public function test_getMask()
    {
    }


    public function test_twiddleWithShit()
    {
    }


    public function test_showTotalErrorPage()
    {
    }


    public function test_getMemoryLimit()
    {
    }


    public function test_getPercentMemoryUsed()
    {
    }

    /**
     * @covers ::remove_install_prefix_from_path
     */
    public function test_normaliseFilePath()
    {
        $result = remove_install_prefix_from_path("/var/app/test");
        $this->assertSame('test', $result);
    }


    /**
     * @return void
     */
    public function test_getEnvString()
    {
        putenv("FOO=BAR");

        $result = getEnvString("FOO");
        $this->assertSame("BAR", $result);
    }


    /**
     * @covers ::array_contains
     */
    public function test_array_contains()
    {
        $this->assertFalse(array_contains(true, [1]));
        $this->assertTrue(array_contains(1, [1]));
    }

    public function test_generate_password_hash()
    {
        $result = generate_password_hash("Hello");
        $this->assertEquals(60, strlen($result));
    }

    /**
     * @covers ::get_password_options
     */
    public function test_get_password_options()
    {
        $options = get_password_options();
        $this->assertArrayHasKey("cost", $options);
        $this->assertGreaterThanOrEqual(12, $options["cost"]);
    }

    public function provides_slugify()
    {
        yield ['Cómo hablar en sílabas', 'como-hablar-en-silabas'];
        yield ['Álix Ãxel', 'alix-axel'];
        yield ['Álix----_Ãxel!?!?', 'alix-axel'];
        yield [
            'FOIA Section 12 and the 18-hour time/cost limit',
            'foia-section-12-and-the-18-hour-time-cost-limit'
        ];

        yield [
            'co-ordinate',
            'co-ordinate'
        ];
    }

    /**
     * @dataProvider provides_slugify
     * @covers slugify
     * @param string $input
     * @param string $expected
     * @return void
     */
    public function test_slugify(string $input, string $expected)
    {
        $result = slugify($input);
        $this->assertSame($expected, $result);
    }





    public function provides_sanitise_filename()
    {
        yield ['John..Anyman', 'john_anyman'];
        yield ['John/Anyman', 'john_anyman'];
        yield ['John\\Anyman', 'john_anyman'];
    }

    /**
     * @covers ::sanitise_filename
     * @dataProvider provides_sanitise_filename
     * @param string $input
     * @param string $expected
     * @return void
     */
    public function test_sanitise_filename(string $input, string $expected)
    {
        $result = sanitise_filename($input);
        $this->assertSame($expected, $result);
    }

    public function provides_standardise_username_to_filename()
    {
        yield ['John Anyman', 'john_anyman'];
        yield ['John..Anyman', 'john_anyman'];
        yield ['John/Anyman', 'john_anyman'];
        yield ['John\\Anyman', 'john_anyman'];
    }

    /**
     * @covers ::standardise_username_to_filename
     * @dataProvider provides_standardise_username_to_filename
     * @param string $input
     * @param string $expected
     * @return void
     */
    public function test_standardise_username_to_filename(string $input, string $expected)
    {
        $result = standardise_username_to_filename($input);
        $this->assertSame($expected, $result);
    }

    public function provides_escapeMySqlLikeString()
    {
        yield ['foo\\bar', 'foo\\\\bar'];
        yield ['foo_bar', 'foo\\_bar'];
        yield ['foo%bar', 'foo\\%bar'];
    }

    /**
     * @dataProvider provides_escapeMySqlLikeString
     * @covers ::escapeMySqlLikeString
     */
    public function test_escapeMySqlLikeString(string $input, string $expected)
    {
        $result = escapeMySqlLikeString($input);
        $this->assertSame($expected, $result);
    }


    /**
     * @covers ::get_external_source_link
     */
    public function test_get_external_source_link()
    {
        $result = get_external_source_link('https://gist.githubusercontent.com/Danack/89e8d9b25dac35e1a68cd3b576a17a36/raw/fb924a43a241d151ba5e659e21a272647658d4e7/words.md');

        $expected = "External source is: <a href='https://gist.githubusercontent.com/Danack/89e8d9b25dac35e1a68cd3b576a17a36'>https://gist.githubusercontent.com/Danack/89e8d9b25dac35e1a68cd3b576a17a36/raw/fb924a43a241d151ba5e659e21a272647658d4e7/words.md</a>";

        $this->assertSame($expected, $result);
    }


    public function test_render_markdown_file()
    {
        $document = new \Bristolian\Model\UserDocument(
            \Bristolian\Types\DocumentType::markdown_file->value,
            "Some title",
            "FOIA Section 12 and the 18-hour time_cost limit.md"
        );
        $user = new \Bristolian\Model\User(\Bristolian\Types\UserList::sid->value);

        $document->setUser($user);

        $result = render_markdown_file($document);
        $this->assertStringStartsWith(
            '<h1>FOIA Section 12 and the 18-hour time/cost limit<a id="user-content-foia-section-12-and-the-18-hour-timecost-limit" href="#content-foia-section-12-and-the-18-hour-timecost-limit" class="h',
            $result
        );
    }
}
