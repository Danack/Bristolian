<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\BccTroDocument;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Model\Types\BccTroDocument
 */
class BccTroDocumentTest extends BaseTestCase
{
    public function testConstruct(): void
    {
        $document = new BccTroDocument(
            'Test Document Title',
            '/files/documents/test-document',
            '12345'
        );

        $this->assertEquals('Test Document Title', $document->title);
        $this->assertEquals('/files/documents/test-document', $document->href);
        $this->assertEquals('12345', $document->id);
    }
}
