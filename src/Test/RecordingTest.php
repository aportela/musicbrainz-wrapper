<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class RecordingTest extends BaseTest
{
    private const string TEST_ARTIST_NAME = "Imagine Dragons";
    private const string TEST_ARTIST_MBID = "012151a8-0f9a-44c9-997f-ebd68b5389f9";
    private const \aportela\MusicBrainzWrapper\ArtistType TEST_ARTIST_TYPE = \aportela\MusicBrainzWrapper\ArtistType::GROUP;
    private const string TEST_ARTIST_COUNTRY = "us";

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
        self::$mbJSON = new \aportela\MusicBrainzWrapper\Recording(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, self::$cachePath);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Recording(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::XML, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, self::$cachePath);
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
        $recording = null;
        try {
            $recording = self::$mbJSON->get(self::TEST_ARTIST_RECORDING_MBID);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        }
        $this->assertSame(self::TEST_ARTIST_RECORDING_TITLE, $recording->title);
        $this->assertCount(1, $recording->artistCredit);
        $this->assertSame(self::TEST_ARTIST_MBID, $recording->artistCredit[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $recording->artistCredit[0]->name);
        $this->assertSame(self::TEST_ARTIST_TYPE, $recording->artistCredit[0]->type);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $recording->artistCredit[0]->country);
    }

    public function testGetXml(): void
    {
        $recording = null;
        try {
            $recording = self::$mbXML->get(self::TEST_ARTIST_RECORDING_MBID);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        }
        $this->assertSame(self::TEST_ARTIST_RECORDING_TITLE, $recording->title);
        $this->assertCount(1, $recording->artistCredit);
        $this->assertSame(self::TEST_ARTIST_MBID, $recording->artistCredit[0]->mbId);
        $this->assertSame(self::TEST_ARTIST_NAME, $recording->artistCredit[0]->name);
        $this->assertSame(self::TEST_ARTIST_TYPE, $recording->artistCredit[0]->type);
        $this->assertSame(self::TEST_ARTIST_COUNTRY, $recording->artistCredit[0]->country);
    }
}
