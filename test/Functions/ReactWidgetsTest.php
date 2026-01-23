<?php

namespace Functions;

use BristolianTest\BaseTestCase;
use function Bristolian\createReactWidget;

/**
 * @coversNothing
 */
class ReactWidgetsTest extends BaseTestCase
{
    /**
     * @covers ::Bristolian\createReactWidget
     */
    public function testWorks_with_simple_data()
    {
        $type = 'test_widget';
        $data = [
            'name' => 'Test',
            'value' => 123,
        ];

        $result = createReactWidget($type, $data);

        $this->assertIsString($result);
        $this->assertStringContainsString('<div>', $result);
        $this->assertStringContainsString('</div>', $result);
        $this->assertStringContainsString("<span class=\"$type\"", $result);
        $this->assertStringContainsString('data-widgety_json=', $result);
        $this->assertStringContainsString('<!-- Hello, I am a react widget. -->', $result);
    }

    /**
     * @covers ::Bristolian\createReactWidget
     */
    public function testWorks_with_complex_data()
    {
        $type = 'complex_widget';
        $data = [
            'items' => [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2'],
            ],
            'metadata' => [
                'count' => 2,
                'timestamp' => new \DateTimeImmutable('2024-01-15 12:00:00'),
            ],
        ];

        $result = createReactWidget($type, $data);

        $this->assertIsString($result);
        $this->assertStringContainsString("<span class=\"$type\"", $result);
        $this->assertStringContainsString('data-widgety_json=', $result);
        
        // Extract and verify the JSON data
        preg_match('/data-widgety_json="([^"]+)"/', $result, $matches);
        $this->assertCount(2, $matches);
        $jsonData = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        $decoded = json_decode($jsonData, true);
        
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('initial_json_data', $decoded);
        $this->assertArrayHasKey('items', $decoded['initial_json_data']);
        $this->assertCount(2, $decoded['initial_json_data']['items']);
    }

    /**
     * @covers ::Bristolian\createReactWidget
     */
    public function testWorks_with_empty_data()
    {
        $type = 'empty_widget';
        $data = [];

        $result = createReactWidget($type, $data);

        $this->assertIsString($result);
        $this->assertStringContainsString("<span class=\"$type\"", $result);
        $this->assertStringContainsString('data-widgety_json=', $result);
        
        // Verify empty data is handled correctly
        preg_match('/data-widgety_json="([^"]+)"/', $result, $matches);
        $jsonData = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        $decoded = json_decode($jsonData, true);
        
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('initial_json_data', $decoded);
        $this->assertIsArray($decoded['initial_json_data']);
        $this->assertEmpty($decoded['initial_json_data']);
    }

    /**
     * @covers ::Bristolian\createReactWidget
     */
    public function testWorks_html_escaping()
    {
        $type = 'html_widget';
        $data = [
            'content' => '<script>alert("xss")</script>',
            'quote' => 'He said "Hello"',
            'apostrophe' => "It's a test",
        ];

        $result = createReactWidget($type, $data);

        $this->assertIsString($result);
        // Verify HTML special characters are escaped in the data attribute
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('alert("xss")', $result);
        
        // Extract and verify the escaped JSON
        preg_match('/data-widgety_json="([^"]+)"/', $result, $matches);
        $jsonData = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        $decoded = json_decode($jsonData, true);
        
        // Verify the original data is preserved in the decoded JSON
        $this->assertSame('<script>alert("xss")</script>', $decoded['initial_json_data']['content']);
        $this->assertSame('He said "Hello"', $decoded['initial_json_data']['quote']);
        $this->assertSame("It's a test", $decoded['initial_json_data']['apostrophe']);
    }

    /**
     * @covers ::Bristolian\createReactWidget
     */
    public function testWorks_with_nested_arrays()
    {
        $type = 'nested_widget';
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value',
                ],
            ],
        ];

        $result = createReactWidget($type, $data);

        $this->assertIsString($result);
        
        // Extract and verify nested structure
        preg_match('/data-widgety_json="([^"]+)"/', $result, $matches);
        $jsonData = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        $decoded = json_decode($jsonData, true);
        
        $this->assertIsArray($decoded['initial_json_data']['level1']);
        $this->assertIsArray($decoded['initial_json_data']['level1']['level2']);
        $this->assertSame('deep value', $decoded['initial_json_data']['level1']['level2']['level3']);
    }

    /**
     * @covers ::Bristolian\createReactWidget
     */
    public function testWorks_with_datetime_objects()
    {
        $type = 'datetime_widget';
        $datetime = new \DateTimeImmutable('2024-01-15 12:00:00');
        $data = [
            'created_at' => $datetime,
        ];

        $result = createReactWidget($type, $data);

        $this->assertIsString($result);
        
        // Extract and verify DateTime is converted to string
        preg_match('/data-widgety_json="([^"]+)"/', $result, $matches);
        $jsonData = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        $decoded = json_decode($jsonData, true);
        
        // convertToValue should convert DateTime to ISO string
        $this->assertIsString($decoded['initial_json_data']['created_at']);
        $this->assertStringContainsString('2024-01-15', $decoded['initial_json_data']['created_at']);
    }
}
