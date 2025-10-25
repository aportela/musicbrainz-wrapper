<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class BaseTest extends \PHPUnit\Framework\TestCase
{
    protected static \Psr\Log\NullLogger $logger;

    protected const THROTTLE_MS = 2500;

    protected const CACHE_PATH = null;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        self::$logger = new \Psr\Log\NullLogger();
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    public function setUp(): void {}

    /**
     * Clean up the test case, called for every defined test
     */
    public function tearDown(): void {}

    /**
     * Clean up the whole test class
     */
    public static function tearDownAfterClass(): void {}
}
