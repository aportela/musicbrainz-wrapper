<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class CoverArtArchiveTest extends BaseTest
{
    private static $mbJSON;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbJSON = new \aportela\MusicBrainzWrapper\CoverArtArchive(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
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


    public function testGetReleaseImageURL(): void
    {
        foreach (\aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::cases() as $size) {
            foreach (\aportela\MusicBrainzWrapper\CoverArtArchiveImageType::cases() as $type) {
                $url = self::$mbJSON->getReleaseImageURL("1b396ee6-5b47-4648-b6c2-a45b7fccafc7", $type, $size);
                $this->assertNotEmpty($url);
                echo $url . PHP_EOL;
            }
        }
    }

    public function testGetJSON(): void
    {
        self::$mbJSON->get("1b396ee6-5b47-4648-b6c2-a45b7fccafc7");
        $this->assertSame(self::$mbJSON->mbId, "1b396ee6-5b47-4648-b6c2-a45b7fccafc7");
        $this->assertIsArray(self::$mbJSON->images);
        $this->assertTrue(count(self::$mbJSON->images) > 0);
    }
}
