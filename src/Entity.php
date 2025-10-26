<?php

namespace aportela\MusicBrainzWrapper;

class Entity
{
    public const USER_AGENT = "MusicBrainzWrapper - https://github.com/aportela/musicbrainz-wrapper (766f6964+github@gmail.com)";

    protected \Psr\Log\LoggerInterface $logger;
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    protected \aportela\MusicBrainzWrapper\APIFormat $apiFormat;
    private \aportela\MusicBrainzWrapper\Cache $cache;

    /**
     * https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
     * For "anonymous" user-agents (see below): we allow through (on average) 50 requests per second, and decline (http 503) the rest.
     */
    private const MIN_THROTTLE_DELAY_MS = 20; // min allowed: 50 requests per second
    private const DEFAULT_THROTTLE_DELAY_MS = 1000; // default: 1 request per second

    private int $originalThrottleDelayMS = 0;
    private int $currentThrottleDelayMS = 0;
    private int $lastThrottleTimestamp = 0;

    protected ?string $cachePath = null;

    protected mixed $parser = null;

    public ?string $mbId = null;
    public ?string $raw = null;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS, ?string $cachePath = null)
    {
        $this->logger = $logger;
        $this->logger->debug("MusicBrainzWrapper::__construct");
        $supportedApiFormats = [\aportela\MusicBrainzWrapper\APIFormat::XML, \aportela\MusicBrainzWrapper\APIFormat::JSON];
        if (!in_array($apiFormat, $supportedApiFormats)) {
            $this->logger->critical("MusicBrainzWrapper::__construct ERROR: invalid api format");
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("supported formats: " . implode(", ", [\aportela\MusicBrainzWrapper\APIFormat::XML->value, \aportela\MusicBrainzWrapper\APIFormat::JSON->value]));
        }
        $this->apiFormat = $apiFormat;
        if ($throttleDelayMS < self::MIN_THROTTLE_DELAY_MS) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidThrottleMsDelayException("min throttle delay ms required: " . self::MIN_THROTTLE_DELAY_MS);
        }
        $this->originalThrottleDelayMS = $throttleDelayMS;
        $this->currentThrottleDelayMS = $throttleDelayMS;
        $this->lastThrottleTimestamp = intval(microtime(true) * 1000);
        if (! empty($cachePath)) {
        }
        $this->cache = new \aportela\MusicBrainzWrapper\Cache($logger, $apiFormat, $cachePath);
        $loadedExtensions = get_loaded_extensions();
        if (!in_array("libxml", $loadedExtensions)) {
            $this->logger->critical("MusicBrainzWrapper::__construct ERROR: libxml extension not found");
            throw new \aportela\MusicBrainzWrapper\Exception\LibXMLMissingException("loaded extensions: " . implode(", ", $loadedExtensions));
        } elseif (!in_array("SimpleXML", $loadedExtensions)) {
            $this->logger->critical("MusicBrainzWrapper::__construct ERROR: SimpleXML extension not found");
            throw new \aportela\MusicBrainzWrapper\Exception\SimpleXMLMissingException("loaded extensions: " . implode(", ", $loadedExtensions));
        } else {
            $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger, self::USER_AGENT);
        }
        // avoids simplexml_load_string warnings
        // https://stackoverflow.com/a/40585185
        libxml_use_internal_errors(true);
        $this->reset();
    }

    public function __destruct()
    {
        $this->logger->debug("MusicBrainzWrapper::__destruct");
    }

    protected function reset(): void
    {
        $this->mbId = null;
        $this->raw = null;
    }

    /**
     * increment throttle delay (time between api calls)
     * call this function when api returns rate limit exception
     * (or connection reset errors caused by remote server busy ?)
     */
    protected function incrementThrottle(): void
    {
        // allow incrementing current throttle delay to a max of 5 seconds
        if ($this->currentThrottleDelayMS < 5000) {
            // set next throttle delay with current value * 2 (wait more time on next api calls)
            $this->currentThrottleDelayMS * 2;
        }
    }

    /**
     * reset throttle to original value
     */
    protected function resetThrottle(): void
    {
        $this->currentThrottleDelayMS = $this->originalThrottleDelayMS;
    }

    /**
     * throttle api calls
     */
    protected function checkThrottle(): void
    {
        if ($this->currentThrottleDelayMS > 0) {
            $currentTimestamp = intval(microtime(true) * 1000);
            while (($currentTimestamp - $this->lastThrottleTimestamp) < $this->currentThrottleDelayMS) {
                usleep(10);
                $currentTimestamp = intval(microtime(true) * 1000);
            }
            $this->lastThrottleTimestamp = $currentTimestamp;
        }
    }

    /**
     * save current raw data into disk cache
     */
    protected function saveCache(string $mbId, string $raw): bool
    {
        return ($this->cache->saveCache($mbId, $raw));
    }

    /**
     * remove cache entry
     */
    protected function removeCache(string $mbId): bool
    {
        return ($this->cache->removeCache($mbId));
    }

    /**
     * read disk cache into current raw data
     */
    protected function getCache(string $mbId): bool
    {
        $this->raw = null;
        if ($cache = $this->cache->getCache($mbId)) {
            $this->raw = $cache;
            return (true);
        } else {
            return (false);
        }
    }


    /**
     * http handler GET method wrapper for catching CurlExecException (connection errors / server busy ?)
     */
    protected function httpGET(string $url): \aportela\HTTPRequestWrapper\HTTPResponse
    {
        try {
            return ($this->http->GET($url));
        } catch (\aportela\HTTPRequestWrapper\Exception\CurlExecException $e) {
            $this->logger->error("Error opening URL " . $url, [$e->getCode(), $e->getMessage()]);
            $this->incrementThrottle(); // sometimes api calls return connection error, interpret this as rate limit response
            throw new \aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException("Error opening URL " . $url, 0, $e);
        }
    }
}
