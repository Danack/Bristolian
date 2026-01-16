<?php

namespace otw_test;

use BristolianTest\BaseTestCase;
use deadish\UserDocument;
use User;

/**
 * @coversNothing
 */
class UserDocumentTest extends BaseTestCase
{
    /**
     * @covers \deadish\UserDocument
     */
    public function testConstruct()
    {
        $stringType = 'markdown_file';
        $title = 'Test Document';
        $source = 'test-source.md';

        $userDocument = new UserDocument($stringType, $title, $source);

        $this->assertSame($stringType, $userDocument->string_type);
        $this->assertSame($title, $userDocument->title);
        $this->assertSame($source, $userDocument->source);
        $this->assertInstanceOf(\Bristolian\Types\DocumentType::class, $userDocument->type);
    }

    /**
     * @covers \deadish\UserDocument
     */
    public function testGetUser()
    {
        $userDocument = new UserDocument('markdown_url', 'Title', 'https://example.com/doc.md');
        $user = $userDocument->getUser();

        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @covers \deadish\UserDocument
     */
    public function testSetUser()
    {
        $userDocument = new UserDocument('markdown_file', 'Title', 'source.md');
        $newUser = new User('newuser');

        $userDocument->setUser($newUser);

        $this->assertSame($newUser, $userDocument->getUser());
    }

    /**
     * @covers \deadish\UserDocument
     */
    public function testConstructWithInvalidType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Unknown document type 'invalid'");

        new UserDocument('invalid', 'Title', 'source.pdf');
    }
}

