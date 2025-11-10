<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

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
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$JSONCache = new \aportela\SimpleFSCache\Cache(self::$logger, self::$cachePath, null, \aportela\SimpleFSCache\CacheFormat::JSON);
        self::$XMLCache = new \aportela\SimpleFSCache\Cache(self::$logger, self::$cachePath, null, \aportela\SimpleFSCache\CacheFormat::XML);

        self::$mbJSON = new \aportela\MusicBrainzWrapper\Artist(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, self::$JSONCache);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Artist(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::XML, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, self::$XMLCache);
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Clean up the test case, called for every defined test
     */
    protected function tearDown(): void
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
        $results = [];
        try {
            $results = self::$mbJSON->search(self::TEST_ARTIST_NAME, 1);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertCount(1, $results);
        $this->assertSame(self::TEST_ARTIST_MBID, $results[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $results[0]->name);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $results[0]->country);
    }

    public function testSearchXml(): void
    {
        $results = [];
        try {
            $results = self::$mbXML->search(self::TEST_ARTIST_NAME, 1);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertCount(1, $results);
        $this->assertSame(self::TEST_ARTIST_MBID, $results[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $results[0]->name);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $results[0]->country);
    }

    public function testGetJson(): void
    {
        $artist = null;
        try {
            $artist = self::$mbJSON->get(self::TEST_ARTIST_MBID);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertSame(self::TEST_ARTIST_MBID, $artist->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $artist->name);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $artist->country);
        $lastFMURLs = $artist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_LASTFM);
        $this->assertCount(1, $lastFMURLs);
        $this->assertSame(self::TEST_ARTIST_LAST_FM_URL, $lastFMURLs[0]);
    }

    public function testGetXml(): void
    {
        $artist = null;
        try {
            $artist = self::$mbXML->get(self::TEST_ARTIST_MBID);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertSame(self::TEST_ARTIST_MBID, $artist->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $artist->name);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $artist->country);
        $lastFMURLs = $artist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_LASTFM);
        $this->assertCount(1, $lastFMURLs);
        $this->assertSame(self::TEST_ARTIST_LAST_FM_URL, $lastFMURLs[0]);
    }
}
