<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class ReleaseTest extends BaseTest
{

    private static $mbReleaseJSON;
    private static $mbReleaseXML;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbReleaseJSON = new \aportela\MusicBrainzWrapper\Release(self::$logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        self::$mbReleaseXML = new \aportela\MusicBrainzWrapper\Release(self::$logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML);
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Clean up the test case, called for every defined test
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Clean up the whole test class
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function testSearchJSON(): void
    {
        $results = self::$mbReleaseJSON->search("piece of mind", "iron maiden", "1983", 1);
        $this->assertCount(1, $results);
        $this->assertSame($results[0]->mbId, "3d241a54-9b55-4ae9-a007-c391f7df29c7");
        $this->assertSame($results[0]->title, "Piece of Mind");
        $this->assertEquals($results[0]->trackCount, 9);
        $this->assertSame($results[0]->artist['mbId'], "ca891d65-d9b0-4258-89f7-e6ba29d83767");
        $this->assertSame($results[0]->artist['name'], "Iron Maiden");
    }

    public function testSearchXML(): void
    {
        $results = self::$mbReleaseXML->search("piece of mind", "iron maiden", "1983", 1);
        $this->assertCount(1, $results);
        $this->assertSame($results[0]->mbId, "3d241a54-9b55-4ae9-a007-c391f7df29c7");
        $this->assertSame($results[0]->title, "Piece of Mind");
        $this->assertSame($results[0]->artist['mbId'], "ca891d65-d9b0-4258-89f7-e6ba29d83767");
        $this->assertSame($results[0]->artist['name'], "Iron Maiden");
        $this->assertEquals($results[0]->trackCount, 9);
    }

    public function testGetJSON(): void
    {
        self::$mbReleaseJSON->get("3d241a54-9b55-4ae9-a007-c391f7df29c7");
        $this->assertSame(self::$mbReleaseJSON->mbId, "3d241a54-9b55-4ae9-a007-c391f7df29c7");
        $this->assertSame(self::$mbReleaseJSON->title, "Piece of Mind");
        $this->assertSame(self::$mbReleaseJSON->artist->mbId, "ca891d65-d9b0-4258-89f7-e6ba29d83767");
        $this->assertSame(self::$mbReleaseJSON->artist->name, "Iron Maiden");
        $this->assertEquals(self::$mbReleaseJSON->trackCount, 9);
    }

    public function testGetXML(): void
    {
        self::$mbReleaseXML->get("3d241a54-9b55-4ae9-a007-c391f7df29c7");
        $this->assertSame(self::$mbReleaseXML->mbId, "3d241a54-9b55-4ae9-a007-c391f7df29c7");
        $this->assertSame(self::$mbReleaseXML->title, "Piece of Mind");
        $this->assertSame(self::$mbReleaseXML->artist->mbId, "ca891d65-d9b0-4258-89f7-e6ba29d83767");
        $this->assertSame(self::$mbReleaseXML->artist->name, "Iron Maiden");
        $this->assertEquals(self::$mbReleaseXML->trackCount, 9);
    }
}
