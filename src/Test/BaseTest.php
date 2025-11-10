<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

abstract class BaseTest extends \PHPUnit\Framework\TestCase
{
    protected static \Psr\Log\NullLogger $logger;

    protected static string $cachePath;

    protected static \aportela\SimpleFSCache\Cache $JSONCache;

    protected static \aportela\SimpleFSCache\Cache $XMLCache;


    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        self::$logger = new \Psr\Log\NullLogger();
        self::$cachePath = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . "cache";
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    protected function setUp(): void
    {
    }

    /**
     * Clean up the test case, called for every defined test
     */
    protected function tearDown(): void
    {
    }

    /**
     * Clean up the whole test class
     */
    public static function tearDownAfterClass(): void
    {
    }
}
