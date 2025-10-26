<?php

namespace aportela\MusicBrainzWrapper;

class Entity
{
    public const USER_AGENT = "MusicBrainzWrapper - https://github.com/aportela/musicbrainz-wrapper (766f6964+github@gmail.com)";

    protected \Psr\Log\LoggerInterface $logger;
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    protected \aportela\MusicBrainzWrapper\APIFormat $apiFormat;


    /**
     * https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
     * For "anonymous" user-agents (see below): we allow through (on average) 50 requests per second, and decline (http 503) the rest.
     */
    protected const MIN_THROTTLE_DELAY_MS = 20; // min allowed: 50 requests per second
    protected const DEFAULT_THROTTLE_DELAY_MS = 1000; // default: 1 request per second

    private int $originalThrottleDelayMS = 0;
    private int $currentThrottleDelayMS = 0;
    private int $lastThrottleTimestamp = 0;

    protected string $defaultXMLNamespaceAlias = "mmd";

    protected ?string $cachePath = null;

    public ?string $mbId = null;
    public ?string $raw = null;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = 0, ?string $cachePath = null)
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
        $this->cachePath = ! empty($cachePath) ? realpath($cachePath) : null;
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
     * return cache file path for MusicBrainz id
     */
    protected function getCacheFilePath(string $mbId): string
    {
        $basePath = $this->getCacheDirectoryPath($mbId);
        switch ($this->apiFormat) {
            case \aportela\MusicBrainzWrapper\APIFormat::JSON:
                return ($basePath . DIRECTORY_SEPARATOR . $mbId . ".json");
            case \aportela\MusicBrainzWrapper\APIFormat::XML:
                return ($basePath . DIRECTORY_SEPARATOR . $mbId . ".xml");
            default:
                return ($basePath . DIRECTORY_SEPARATOR . $mbId);
        }
    }

    /**
     * return cache directory path for MusicBrainz id
     */
    protected function getCacheDirectoryPath(string $mbId): string
    {
        return ($this->cachePath . DIRECTORY_SEPARATOR . mb_substr($mbId, 0, 1) . DIRECTORY_SEPARATOR . mb_substr($mbId, 1, 1) . DIRECTORY_SEPARATOR . mb_substr($mbId, 2, 1) . DIRECTORY_SEPARATOR . mb_substr($mbId, 3, 1));
    }

    /**
     * save current raw data into disk cache
     */
    protected function saveCache(string $mbId, string $raw): bool
    {
        try {
            if (! empty($this->cachePath) && ! empty($raw)) {
                $this->logger->debug("Saving MusicBrainz disk cache", [$mbId, $this->cachePath, $this->getCacheFilePath($mbId)]);
                $directoryPath = $this->getCacheDirectoryPath($mbId);
                if (! file_exists($directoryPath)) {
                    if (!mkdir($directoryPath, 0750, true)) {
                        $this->logger->error("Error creating MusicBrainz disk cache directory", [$mbId, $directoryPath]);
                        return (false);
                    }
                }
                return (file_put_contents($this->getCacheFilePath($mbId), $raw) > 0);
            } else {
                return (false);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Error saving MusicBrainz disk cache", [$mbId, $e->getMessage()]);
            return (false);
        }
    }

    /**
     * remove cache entry
     */
    protected function removeCache(string $mbId): bool
    {
        try {
            if (! empty($this->cachePath)) {
                $cacheFilePath = $this->getCacheFilePath($mbId);
                if (file_exists($cacheFilePath)) {
                    return (unlink($cacheFilePath));
                } else {
                    return (false);
                }
            } else {
                return (false);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Error removing MusicBrainz disk cache", [$mbId, $e->getMessage()]);
            return (false);
        }
    }

    /**
     * read disk cache into current raw data
     */
    protected function getCache(string $mbId): bool
    {
        $this->raw = null;
        try {
            if (! empty($this->cachePath)) {
                if (file_exists($this->getCacheFilePath($mbId))) {
                    $this->logger->debug("Loading MusicBrainz disk cache", [$mbId, $this->cachePath, $this->getCacheFilePath($mbId)]);
                    $this->raw = file_get_contents($this->getCacheFilePath($mbId));
                    return (! empty($this->raw));
                } else {
                    $this->logger->debug("MusicBrainz disk cache not found", [$mbId, $this->cachePath, $this->getCacheFilePath($mbId)]);
                    return (false);
                }
            } else {
                return (false);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Error loading MusicBrainz disk cache", [$mbId, $this->cachePath, $e->getMessage()]);
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

    // TODO: REMOVE
    /**
     * parse json, launch InvalidJSONException on errors
     */
    protected function parseJSON(string $rawText): mixed
    {
        $json = json_decode($rawText);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidJSONException(json_last_error_msg(), json_last_error());
        }
        return ($json);
    }

    // TODO: REMOVE
    /**
     * parse xml, launch InvalidXMLException on errors
     */
    protected function parseXML(string $rawText): mixed
    {
        libxml_clear_errors();
        $xml = simplexml_load_string($rawText);
        $xml->registerXPathNamespace($this->defaultXMLNamespaceAlias, reset($xml->getNamespaces(true)));
        if ($xml === false) {
            $errorMessage = "invalid xml";
            $errorCode = 0;
            $lastError = libxml_get_last_error();
            if ($lastError) {
                $errorMessage = "Error: " . $lastError->message . " (Line: " . $lastError->line . ", Column: " . $lastError->column . ")";
                $errorCode = $lastError->code;
            }
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException($errorMessage, $errorCode);
        }
        return ($xml);
    }
}
