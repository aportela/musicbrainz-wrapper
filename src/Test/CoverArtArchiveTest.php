<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class CoverArtArchiveTest extends BaseTest
{
    private const string TEST_RELEASE_MBID = "1b396ee6-5b47-4648-b6c2-a45b7fccafc7";

    private static \aportela\MusicBrainzWrapper\CoverArtArchive $coverArtArchive;

    /**
     * Called once just like normal constructor
     */
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$JSONCache = new \aportela\SimpleFSCache\Cache(self::$logger, self::$cachePath, null, \aportela\SimpleFSCache\CacheFormat::JSON);

        self::$coverArtArchive = new \aportela\MusicBrainzWrapper\CoverArtArchive(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, self::$JSONCache);
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

    public function testGetReleaseImageUrl(): void
    {
        try {
            foreach (\aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::cases() as $size) {
                foreach (\aportela\MusicBrainzWrapper\CoverArtArchiveImageType::cases() as $type) {
                    $url = self::$coverArtArchive->getReleaseImageURL(self::TEST_RELEASE_MBID, $type, $size);
                    $this->assertNotEmpty($url);
                }
            }
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $remoteAPIServerConnectionException) {
            $this->markTestSkipped('API server connection error: ' . $remoteAPIServerConnectionException->getMessage());
        }
    }

    public function testGetJson(): void
    {
        $coverArtArchive = null;
        try {
            $coverArtArchive = self::$coverArtArchive->get(self::TEST_RELEASE_MBID);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->markTestSkipped('API server connection error: ' . $e->getMessage());
        } catch (\aportela\MusicBrainzWrapper\Exception\RateLimitExceedException $e) {
            $this->markTestSkipped('API server is limited by rate: ' . $e->getMessage());
        }

        $this->assertSame(self::TEST_RELEASE_MBID, $coverArtArchive->mbId);
        $this->assertTrue($coverArtArchive->images !== []);
    }
}
