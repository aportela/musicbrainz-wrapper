<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class ReleaseTest extends BaseTest
{
    private const string TEST_ARTIST_NAME = "Kavinsky";

    private const string TEST_ARTIST_MBID = "eb6de5f6-98f8-4b5a-bfdc-f87fa4936baa";

    private const \aportela\MusicBrainzWrapper\ArtistType TEST_ARTIST_TYPE = \aportela\MusicBrainzWrapper\ArtistType::PERSON;

    private const string TEST_ARTIST_COUNTRY = "fr";

    private const string TEST_ARTIST_RELEASE_TITLE = "OutRun";

    private const string TEST_ARTIST_RELEASE_YEAR = "2013";

    private const string TEST_ARTIST_RELEASE_MBID = "4e5d9f0c-09b6-42bf-b495-e2d7cc288bf6";

    private const int TEST_ARTIST_RELEASE_MEDIA_COUNT = 1;

    private const int TEST_ARTIST_RELEASE_MEDIA_TRACK_COUNT = 13;

    private static \aportela\MusicBrainzWrapper\Release $mbJSON;

    private static \aportela\MusicBrainzWrapper\Release $mbXML;

    /**
     * Called once just like normal constructor
     */
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$JSONCache = new \aportela\SimpleFSCache\Cache(self::$logger, self::$cachePath, null, \aportela\SimpleFSCache\CacheFormat::JSON);
        self::$XMLCache = new \aportela\SimpleFSCache\Cache(self::$logger, self::$cachePath, null, \aportela\SimpleFSCache\CacheFormat::XML);

        self::$mbJSON = new \aportela\MusicBrainzWrapper\Release(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, self::$JSONCache);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Release(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::XML, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, self::$XMLCache);
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
            $results = self::$mbJSON->search(self::TEST_ARTIST_RELEASE_TITLE, self::TEST_ARTIST_NAME, self::TEST_ARTIST_RELEASE_YEAR, 9);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertCount(9, $results);
        $this->assertCount(1, $results[0]->artistCredit);
        $this->assertSame(self::TEST_ARTIST_MBID, $results[0]->artistCredit[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $results[0]->artistCredit[0]->name);
        $found = false;
        foreach ($results as $result) {
            // sometimes the requested release mbId in the group is not the "first"
            // (search on all release-group items)
            if (self::TEST_ARTIST_RELEASE_MBID == $result->mbId && self::TEST_ARTIST_RELEASE_TITLE == $result->title) {
                $found  = true;
            }
        }

        $this->assertTrue($found);
    }

    public function testSearchXml(): void
    {
        $results = [];
        try {
            $results = self::$mbXML->search(self::TEST_ARTIST_RELEASE_TITLE, self::TEST_ARTIST_NAME, self::TEST_ARTIST_RELEASE_YEAR, 9);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertCount(9, $results);
        $this->assertCount(1, $results[0]->artistCredit);
        $this->assertSame(self::TEST_ARTIST_MBID, $results[0]->artistCredit[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $results[0]->artistCredit[0]->name);
        $found = false;
        foreach ($results as $result) {
            // sometimes the requested release mbId in the group is not the "first"
            // (search on all release-group items)
            if (self::TEST_ARTIST_RELEASE_MBID == $result->mbId && self::TEST_ARTIST_RELEASE_TITLE == $result->title) {
                $found  = true;
            }
        }

        $this->assertTrue($found);
    }

    public function testGetJson(): void
    {
        $release = null;
        try {
            $release = self::$mbJSON->get(self::TEST_ARTIST_RELEASE_MBID);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertSame(self::TEST_ARTIST_RELEASE_MBID, $release->mbId);
        $this->assertSame(self::TEST_ARTIST_RELEASE_TITLE, $release->title);
        $this->assertCount(1, $release->artistCredit);
        $this->assertSame(self::TEST_ARTIST_MBID, $release->artistCredit[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $release->artistCredit[0]->name);
        $this->assertSame(self::TEST_ARTIST_TYPE, $release->artistCredit[0]->type);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $release->artistCredit[0]->country);
        $this->assertCount(self::TEST_ARTIST_RELEASE_MEDIA_COUNT, $release->media);
        $this->assertCount(self::TEST_ARTIST_RELEASE_MEDIA_TRACK_COUNT, $release->media[0]->trackList);
        $this->assertTrue($release->coverArtArchive->artwork);
        $this->assertTrue($release->coverArtArchive->front);
    }

    public function testGetXml(): void
    {
        $release = null;
        try {
            $release = self::$mbXML->get(self::TEST_ARTIST_RELEASE_MBID);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertSame(self::TEST_ARTIST_RELEASE_MBID, $release->mbId);
        $this->assertSame(self::TEST_ARTIST_RELEASE_TITLE, $release->title);
        $this->assertCount(1, $release->artistCredit);
        $this->assertSame(self::TEST_ARTIST_MBID, $release->artistCredit[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $release->artistCredit[0]->name);
        $this->assertSame(self::TEST_ARTIST_TYPE, $release->artistCredit[0]->type);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $release->artistCredit[0]->country);
        $this->assertCount(self::TEST_ARTIST_RELEASE_MEDIA_COUNT, $release->media);
        $this->assertCount(self::TEST_ARTIST_RELEASE_MEDIA_TRACK_COUNT, $release->media[0]->trackList);
        $this->assertTrue($release->coverArtArchive->artwork);
        $this->assertTrue($release->coverArtArchive->front);
    }
}
