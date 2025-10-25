<?php

namespace aportela\MusicBrainzWrapper;

use function PHPUnit\Framework\directoryExists;

class Entity
{
    public const USER_AGENT = "MusicBrainzWrapper - https://github.com/aportela/musicbrainz-wrapper (766f6964+github@gmail.com)";

    protected \Psr\Log\LoggerInterface $logger;
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    protected \aportela\MusicBrainzWrapper\APIFormat $apiFormat;

    protected int $throttleDelayMS = 0;
    protected int $lastThrottleTimestamp = 0;

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
        $this->throttleDelayMS = $throttleDelayMS;
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
    }

    public function __destruct()
    {
        $this->logger->debug("MusicBrainzWrapper::__destruct");
    }

    /**
     * throttle api calls
     */
    protected function checkThrottle()
    {
        if ($this->throttleDelayMS > 0) {
            $currentTimestamp = intval(microtime(true) * 1000);
            while (($currentTimestamp - $this->lastThrottleTimestamp) < $this->throttleDelayMS) {
                usleep(10);
                $currentTimestamp = intval(microtime(true) * 1000);
            }
            $this->lastThrottleTimestamp = $currentTimestamp;
        }
    }

    /**
     * return cache path for MusicBrainz id
     */
    protected function getCachePath(string $mbId)
    {
        switch ($this->apiFormat) {
            case \aportela\MusicBrainzWrapper\APIFormat::JSON:
                return ($this->cachePath . DIRECTORY_SEPARATOR . $mbId . ".json");
            case \aportela\MusicBrainzWrapper\APIFormat::XML:
                return ($this->cachePath . DIRECTORY_SEPARATOR . $mbId . ".xml");
            default:
                return ($this->cachePath . DIRECTORY_SEPARATOR . $mbId);
        }
    }

    /**
     * save current raw data into disk cache
     */
    protected function saveCache(string $mbId, string $raw): bool
    {
        try {
            if (! empty($this->cachePath) && ! empty($raw)) {
                $this->logger->debug("Saving MusicBrainz disk cache", [$mbId, $this->cachePath, $this->getCachePath($mbId)]);
                return (file_put_contents($this->getCachePath($mbId), $raw) > 0);
            } else {
                return (false);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Error saving MusicBrainz disk cache", [$mbId, $e->getMessage()]);
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
                if (file_exists($this->getCachePath($mbId))) {
                    $this->logger->debug("Loading MusicBrainz disk cache", [$mbId, $this->cachePath, $this->getCachePath($mbId)]);
                    $this->raw = file_get_contents($this->getCachePath($mbId));
                    return (! empty($this->raw));
                } else {
                    $this->logger->debug("MusicBrainz disk cache not found", [$mbId, $this->cachePath, $this->getCachePath($mbId)]);
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
}
