<?php

namespace aportela\MusicBrainzWrapper;

class Entity
{
    public const USER_AGENT = "MusicBrainzWrapper - https://github.com/aportela/musicbrainz-wrapper (766f6964+github@gmail.com)";

    protected \Psr\Log\LoggerInterface $logger;
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    protected \aportela\MusicBrainzWrapper\APIFormat $apiFormat;

    private ?\aportela\SimpleFSCache\Cache $cache;
    private \aportela\SimpleThrottle\Throttle $throttle;

    /**
     * https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
     * For "anonymous" user-agents (see below): we allow through (on average) 50 requests per second, and decline (http 503) the rest.
     */
    private const MIN_THROTTLE_DELAY_MS = 20; // min allowed: 50 requests per second
    public const DEFAULT_THROTTLE_DELAY_MS = 1000; // default: 1 request per second

    protected mixed $parser = null;

    public ?string $raw = null;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS, ?\aportela\SimpleFSCache\Cache $cache = null)
    {
        $this->logger = $logger;
        $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger, self::USER_AGENT);
        $supportedApiFormats = [\aportela\MusicBrainzWrapper\APIFormat::XML, \aportela\MusicBrainzWrapper\APIFormat::JSON];
        if (!in_array($apiFormat, $supportedApiFormats)) {
            $this->logger->critical("\aportela\MusicBrainzWrapper\Entity::__construct - ERROR: invalid api format", [$apiFormat, [\aportela\MusicBrainzWrapper\APIFormat::XML->value, \aportela\MusicBrainzWrapper\APIFormat::JSON->value]]);
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("supported formats: " . implode(", ", [\aportela\MusicBrainzWrapper\APIFormat::XML->value, \aportela\MusicBrainzWrapper\APIFormat::JSON->value]));
        }
        $this->apiFormat = $apiFormat;
        if ($throttleDelayMS < self::MIN_THROTTLE_DELAY_MS) {
            $this->logger->critical("\aportela\MusicBrainzWrapper\Entity::__construct - ERROR: invalid throttleDelayMS", [$throttleDelayMS, self::MIN_THROTTLE_DELAY_MS]);
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidThrottleMsDelayException("min throttle delay ms required: " . self::MIN_THROTTLE_DELAY_MS);
        }
        $this->throttle = new \aportela\SimpleThrottle\Throttle($this->logger, $throttleDelayMS, 5000, 10);
        $this->cache = $cache;
        if ($apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $loadedExtensions = get_loaded_extensions();
            foreach (["libxml", "SimpleXML"] as $requiredExtension) {
                if (!in_array($requiredExtension, $loadedExtensions)) {
                    $this->logger->critical("\aportela\MusicBrainzWrapper\Entity::__construct - ERROR: {$requiredExtension} php extension not found", $loadedExtensions);
                    throw new \aportela\MusicBrainzWrapper\Exception\PHPExtensionMissingException("Missing required php extension: {$requiredExtension}, loaded extensions: " . implode(", ", $loadedExtensions));
                }
            }
            // avoids simplexml_load_string warnings
            // https://stackoverflow.com/a/40585185
            libxml_use_internal_errors(true);
        }
        $this->reset();
    }

    public function __destruct() {}

    protected function reset(): void
    {
        $this->raw = null;
    }

    /**
     * increment throttle delay (time between api calls)
     * call this function when api returns rate limit exception
     * (or connection reset errors caused by remote server busy ?)
     */
    protected function incrementThrottle(): void
    {
        $this->throttle->increment(\aportela\SimpleThrottle\ThrottleDelayIncrementType::MULTIPLY_BY_2);
    }

    /**
     * reset throttle to original value
     */
    protected function resetThrottle(): void
    {
        $this->throttle->reset();
    }

    /**
     * throttle api calls
     */
    protected function checkThrottle(): void
    {
        $this->throttle->throttle();
    }

    /**
     * save current raw data into disk cache
     */
    protected function saveCache(string $mbId, string $raw): bool
    {
        if ($this->cache !== null) {
            return ($this->cache->save($mbId, $raw));
        } else {
            return (false);
        }
    }

    /**
     * remove cache entry
     */
    protected function removeCache(string $mbId): bool
    {
        if ($this->cache !== null) {
            return ($this->cache->remove($mbId));
        } else {
            return (false);
        }
    }

    /**
     * read disk cache into current raw data
     */
    protected function getCache(string $mbId): bool
    {
        $this->reset();
        if ($this->cache !== null) {
            $cacheData = $this->cache->get($mbId);
            if (is_string($cacheData)) {
                $this->raw = $cacheData;
                return (true);
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    /**
     * http handler GET method wrapper for catching CurlExecException (connection errors / server busy ?)
     */
    protected function httpGET(string $url): ?string
    {
        $this->logger->debug("\aportela\MusicBrainzWrapper\Entity::httpGET - Opening URL", [$url]);
        try {
            $this->checkThrottle();
            $response = $this->http->GET($url);
            if ($response->code == 200) {
                $this->resetThrottle();
                return ($response->body);
            } elseif ($response->code == 400) {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($url, $response->code);
            } elseif ($response->code == 404) {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($url, $response->code);
            } elseif ($response->code == 503) {
                $this->incrementThrottle();
                $this->logger->info("\aportela\MusicBrainzWrapper\Entity::httpGET - Error: rate limited (503) response, incrementing throttle", [$url]);
                throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException("URL: {$url}", $response->code);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($url, $response->code);
            }
        } catch (\aportela\HTTPRequestWrapper\Exception\CurlExecException $e) {
            $this->logger->error("\aportela\MusicBrainzWrapper\Entity::httpGET - Error opening URL", [$url, $e->getCode(), $e->getMessage()]);
            $this->incrementThrottle(); // sometimes api calls return connection error, interpret this as rate limit response
            throw new \aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException("Error opening URL " . $url, 0, $e);
        }
    }
}
