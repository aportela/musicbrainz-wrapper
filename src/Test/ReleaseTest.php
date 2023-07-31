<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class ReleaseTest extends BaseTest
{

    private static $mbJSON;
    private static $mbXML;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbJSON = new \aportela\MusicBrainzWrapper\Release(self::$logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Release(self::$logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML);
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
        $results = self::$mbJSON->search("piece of mind", "iron maiden", "1983", 1);
        $this->assertCount(1, $results);
        $this->assertSame($results[0]->mbId, "1b396ee6-5b47-4648-b6c2-a45b7fccafc7");
        $this->assertSame($results[0]->title, "Piece of Mind");
        $this->assertSame($results[0]->artist->mbId, "ca891d65-d9b0-4258-89f7-e6ba29d83767");
        $this->assertSame($results[0]->artist->name, "Iron Maiden");
        $this->assertEquals($results[0]->trackCount, 9);
    }

    public function testSearchXML(): void
    {
        $results = self::$mbXML->search("piece of mind", "iron maiden", "1983", 1);
        $this->assertCount(1, $results);
        $this->assertSame($results[0]->mbId, "1b396ee6-5b47-4648-b6c2-a45b7fccafc7");
        $this->assertSame($results[0]->title, "Piece of Mind");
        $this->assertSame($results[0]->artist->mbId, "ca891d65-d9b0-4258-89f7-e6ba29d83767");
        $this->assertSame($results[0]->artist->name, "Iron Maiden");
        $this->assertEquals($results[0]->trackCount, 9);
    }

    public function testGetJSON(): void
    {
        self::$mbJSON->get("1b396ee6-5b47-4648-b6c2-a45b7fccafc7");
        $this->assertSame(self::$mbJSON->mbId, "1b396ee6-5b47-4648-b6c2-a45b7fccafc7");
        $this->assertSame(self::$mbJSON->title, "Piece of Mind");
        $this->assertSame(self::$mbJSON->artist->mbId, "ca891d65-d9b0-4258-89f7-e6ba29d83767");
        $this->assertSame(self::$mbJSON->artist->name, "Iron Maiden");
        $this->assertEquals(self::$mbJSON->year, 1983);
        $this->assertEquals(self::$mbJSON->trackCount, 9);
    }

    public function testGetXML(): void
    {
        self::$mbXML->get("1b396ee6-5b47-4648-b6c2-a45b7fccafc7");
        $this->assertSame(self::$mbXML->mbId, "1b396ee6-5b47-4648-b6c2-a45b7fccafc7");
        $this->assertSame(self::$mbXML->title, "Piece of Mind");
        $this->assertSame(self::$mbXML->artist->mbId, "ca891d65-d9b0-4258-89f7-e6ba29d83767");
        $this->assertSame(self::$mbXML->artist->name, "Iron Maiden");
        $this->assertEquals(self::$mbJSON->year, 1983);
        $this->assertEquals(self::$mbXML->trackCount, 9);
    }
}
