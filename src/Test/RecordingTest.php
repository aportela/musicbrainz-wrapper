<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class RecordingTest extends BaseTest
{
    private static $mbJSON;
    private static $mbXML;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mbJSON = new \aportela\MusicBrainzWrapper\Recording(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
        self::$mbXML = new \aportela\MusicBrainzWrapper\Recording(self::$logger, \aportela\MusicBrainzWrapper\APIFormat::XML);
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

    public function testGetJSON(): void
    {
        self::$mbJSON->get("bd61eda3-eb77-4634-ba66-4a084f7f8455");
        $this->assertSame(self::$mbJSON->title, "Radioactive");
        $this->assertSame(self::$mbJSON->artist->mbId, "012151a8-0f9a-44c9-997f-ebd68b5389f9");
        $this->assertSame(self::$mbJSON->artist->name, "Imagine Dragons");
    }

    public function testGetXML(): void
    {
        self::$mbXML->get("bd61eda3-eb77-4634-ba66-4a084f7f8455");
        $this->assertSame(self::$mbXML->title, "Radioactive");
        $this->assertSame(self::$mbXML->artist->mbId, "012151a8-0f9a-44c9-997f-ebd68b5389f9");
        $this->assertSame(self::$mbXML->artist->name, "Imagine Dragons");
    }
}
