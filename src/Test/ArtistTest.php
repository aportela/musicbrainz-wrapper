<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class ArtistTest extends BaseTest
{
    private const string TEST_ARTIST_NAME = "Roxette";
    private const string TEST_ARTIST_MBID = "d3b2711f-2baa-441a-be95-14945ca7e6ea";
    private const string TEST_ARTIST_COUNTRY = "se";
    private const string TEST_ARTIST_LAST_FM_URL = "https://www.last.fm/music/Roxette";

    private static \aportela\MusicBrainzWrapper\Artist $mbJSON;
    private static \aportela\MusicBrainzWrapper\Artist $mbXML;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbJSON = new \aportela\MusicBrainzWrapper\Artist(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, self::THROTTLE_MS, self::CACHE_PATH);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Artist(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::XML, self::THROTTLE_MS, self::CACHE_PATH);
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

    public function testSearchJson(): void
    {
        $results = self::$mbJSON->search(self::TEST_ARTIST_NAME, 1);
        $this->assertCount(1, $results);
        $this->assertSame(self::TEST_ARTIST_MBID, $results[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $results[0]->name);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $results[0]->country);
    }

    public function testSearchXml(): void
    {
        $results = self::$mbXML->search(self::TEST_ARTIST_NAME, 1);
        $this->assertCount(1, $results);
        $this->assertSame(self::TEST_ARTIST_MBID, $results[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $results[0]->name);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $results[0]->country);
    }

    public function testGetJson(): void
    {
        self::$mbJSON->get(self::TEST_ARTIST_MBID);
        $this->assertSame(self::TEST_ARTIST_MBID, self::$mbJSON->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, self::$mbJSON->name);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, self::$mbJSON->country);
        $lastFMURLs = self::$mbJSON->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_LASTFM);
        $this->assertCount(1, $lastFMURLs);
        $this->assertSame(self::TEST_ARTIST_LAST_FM_URL, $lastFMURLs[0]);
    }

    public function testGetXml(): void
    {
        self::$mbXML->get(self::TEST_ARTIST_MBID);
        $this->assertSame(self::TEST_ARTIST_MBID, self::$mbXML->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, self::$mbXML->name);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, self::$mbXML->country);
        $lastFMURLs = self::$mbXML->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_LASTFM);
        $this->assertCount(1, $lastFMURLs);
        $this->assertSame(self::TEST_ARTIST_LAST_FM_URL, $lastFMURLs[0]);
    }
}
