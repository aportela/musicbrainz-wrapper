<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class CoverArtArchiveTest extends BaseTest
{
    private const string TEST_RELEASE_MBID = "1b396ee6-5b47-4648-b6c2-a45b7fccafc7";

    private static \aportela\MusicBrainzWrapper\CoverArtArchive $mbJSON;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbJSON = new \aportela\MusicBrainzWrapper\CoverArtArchive(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, self::$cachePath);
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

    public function testGetReleaseImageUrl(): void
    {
        try {
            foreach (\aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::cases() as $size) {
                foreach (\aportela\MusicBrainzWrapper\CoverArtArchiveImageType::cases() as $type) {
                    $url = self::$mbJSON->getReleaseImageURL(self::TEST_RELEASE_MBID, $type, $size);
                    $this->assertNotEmpty($url);
                }
            }
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        }
    }

    public function testGetJson(): void
    {
        $coverArtArchive = null;
        try {
            $coverArtArchive = self::$mbJSON->get(self::TEST_RELEASE_MBID);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        }
        $this->assertSame(self::TEST_RELEASE_MBID, $coverArtArchive->mbId);
        $this->assertTrue(count($coverArtArchive->images) > 0);
    }
}
