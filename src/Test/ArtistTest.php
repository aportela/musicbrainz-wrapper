<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class ArtistTest extends BaseTest
{

    private static $mbJSON;
    private static $mbXML;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbJSON = new \aportela\MusicBrainzWrapper\Artist(self::$logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Artist(self::$logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML);
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
        $results = self::$mbJSON->search("Roxette", 1);
        $this->assertCount(1, $results);
        $this->assertSame($results[0]->mbId, "d3b2711f-2baa-441a-be95-14945ca7e6ea");
        $this->assertSame($results[0]->name, "Roxette");
        $this->assertSame($results[0]->country, "se");
    }

    public function testSearchXML(): void
    {
        $results = self::$mbXML->search("Roxette", 1);
        $this->assertCount(1, $results);
        $this->assertSame($results[0]->mbId, "d3b2711f-2baa-441a-be95-14945ca7e6ea");
        $this->assertSame($results[0]->name, "Roxette");
        $this->assertSame($results[0]->country, "se");
    }

    public function testGetJSON(): void
    {
        self::$mbJSON->get("d3b2711f-2baa-441a-be95-14945ca7e6ea");
        $this->assertSame(self::$mbJSON->mbId, "d3b2711f-2baa-441a-be95-14945ca7e6ea");
        $this->assertSame(self::$mbJSON->name, "Roxette");
        $this->assertSame(self::$mbJSON->country, "se");
    }

    public function testGetXML(): void
    {
        self::$mbXML->get("d3b2711f-2baa-441a-be95-14945ca7e6ea");
        $this->assertSame(self::$mbXML->mbId, "d3b2711f-2baa-441a-be95-14945ca7e6ea");
        $this->assertSame(self::$mbXML->name, "Roxette");
        $this->assertSame(self::$mbXML->country, "se");
    }
}
