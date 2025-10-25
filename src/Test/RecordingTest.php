<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class RecordingTest extends BaseTest
{
    private const string TEST_ARTIST_NAME = "Imagine Dragons";
    private const string TEST_ARTIST_MBID = "012151a8-0f9a-44c9-997f-ebd68b5389f9";

    private const string TEST_ARTIST_RECORDING_TITLE = "Radioactive";
    private const string TEST_ARTIST_RECORDING_MBID = "bd61eda3-eb77-4634-ba66-4a084f7f8455";

    private static \aportela\MusicBrainzWrapper\Recording $mbJSON;
    private static \aportela\MusicBrainzWrapper\Recording $mbXML;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbJSON = new \aportela\MusicBrainzWrapper\Recording(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, self::THROTTLE_MS, self::CACHE_PATH);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Recording(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::XML, self::THROTTLE_MS, self::CACHE_PATH);
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

    public function testGetJson(): void
    {
        self::$mbJSON->get(self::TEST_ARTIST_RECORDING_MBID);
        $this->assertSame(self::TEST_ARTIST_RECORDING_TITLE, self::$mbJSON->title);
        $this->assertSame(self::TEST_ARTIST_MBID, self::$mbJSON->artist->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, self::$mbJSON->artist->name);
    }

    public function testGetXml(): void
    {
        self::$mbXML->get(self::TEST_ARTIST_RECORDING_MBID);
        $this->assertSame(self::TEST_ARTIST_RECORDING_TITLE, self::$mbXML->title);
        $this->assertSame(self::TEST_ARTIST_MBID, self::$mbXML->artist->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, self::$mbXML->artist->name);
    }
}
