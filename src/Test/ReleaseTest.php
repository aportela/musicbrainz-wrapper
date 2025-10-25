<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class ReleaseTest extends BaseTest
{
    private const string TEST_ARTIST_NAME = "Kavinsky";
    private const string TEST_ARTIST_MBID = "eb6de5f6-98f8-4b5a-bfdc-f87fa4936baa";

    private const string TEST_ARTIST_RELEASE_TITLE = "OutRun";
    private const string TEST_ARTIST_RELEASE_YEAR = "2013";
    private const string TEST_ARTIST_RELEASE_MBID = "4e5d9f0c-09b6-42bf-b495-e2d7cc288bf6";
    private const int TEST_ARTIST_RELEASE_TRACK_COUNT = 13;

    private static \aportela\MusicBrainzWrapper\Release $mbJSON;
    private static \aportela\MusicBrainzWrapper\Release $mbXML;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbJSON = new \aportela\MusicBrainzWrapper\Release(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, self::THROTTLE_MS, self::CACHE_PATH);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Release(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::XML, self::THROTTLE_MS, self::CACHE_PATH);
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
        $results = self::$mbJSON->search(self::TEST_ARTIST_RELEASE_TITLE, self::TEST_ARTIST_NAME, self::TEST_ARTIST_RELEASE_YEAR, 9);
        $this->assertCount(9, $results);
        $this->assertSame(self::TEST_ARTIST_MBID, $results[0]->artist->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $results[0]->artist->name);
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
        $results = self::$mbXML->search(self::TEST_ARTIST_RELEASE_TITLE, self::TEST_ARTIST_NAME, self::TEST_ARTIST_RELEASE_YEAR, 9);
        $this->assertCount(9, $results);
        $this->assertSame(self::TEST_ARTIST_MBID, $results[0]->artist->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $results[0]->artist->name);
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
        self::$mbJSON->get(self::TEST_ARTIST_RELEASE_MBID);
        $this->assertSame(self::TEST_ARTIST_RELEASE_MBID, self::$mbJSON->mbId);
        $this->assertSame(self::TEST_ARTIST_RELEASE_TITLE, self::$mbJSON->title);
        $this->assertSame(self::TEST_ARTIST_MBID, self::$mbJSON->artist->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, self::$mbJSON->artist->name);
        $this->assertEquals(self::TEST_ARTIST_RELEASE_YEAR, self::$mbJSON->year);
        $this->assertEquals(1, count(self::$mbJSON->media));
        $this->assertEquals(self::TEST_ARTIST_RELEASE_TRACK_COUNT, self::$mbJSON->media[0]->trackCount);
    }

    public function testGetXml(): void
    {
        self::$mbXML->get(self::TEST_ARTIST_RELEASE_MBID);
        $this->assertSame(self::TEST_ARTIST_RELEASE_MBID, self::$mbXML->mbId);
        $this->assertSame(self::TEST_ARTIST_RELEASE_TITLE, self::$mbXML->title);
        $this->assertSame(self::TEST_ARTIST_MBID, self::$mbXML->artist->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, self::$mbXML->artist->name);
        $this->assertEquals(self::TEST_ARTIST_RELEASE_YEAR, self::$mbXML->year);
        $this->assertEquals(1, count(self::$mbXML->media));
        $this->assertEquals(self::TEST_ARTIST_RELEASE_TRACK_COUNT, self::$mbXML->media[0]->trackCount);
    }
}
