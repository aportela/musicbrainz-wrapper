<?php

namespace aportela\MusicBrainzWrapper;

class Cache
{
    private \Psr\Log\LoggerInterface $logger;
    private ?string $cachePath = null;
    private \aportela\MusicBrainzWrapper\APIFormat $apiFormat;
    private bool $enabled = true;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, ?string $cachePath = null)
    {
        $this->logger = $logger;
        if (! empty($cachePath)) {
            $this->cachePath = ($path = realpath($cachePath)) ? $path : null;
        }
        $this->enabled = ! empty($this->cachePath);
        $this->apiFormat = $apiFormat;
    }

    /**
     * return cache directory path for MusicBrainz id
     */
    private function getCacheDirectoryPath(string $mbId): string
    {
        return ($this->cachePath . DIRECTORY_SEPARATOR . mb_substr($mbId, 0, 1) . DIRECTORY_SEPARATOR . mb_substr($mbId, 1, 1) . DIRECTORY_SEPARATOR . mb_substr($mbId, 2, 1) . DIRECTORY_SEPARATOR . mb_substr($mbId, 3, 1));
    }

    /**
     * return cache file path for MusicBrainz id
     */
    private function getCacheFilePath(string $mbId): string
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
     * save current raw data into disk cache
     */
    public function saveCache(string $mbId, string $raw): bool
    {
        if ($this->enabled) {
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
        } else {
            return (false);
        }
    }

    /**
     * remove cache entry
     */
    public function removeCache(string $mbId): bool
    {
        if ($this->enabled) {
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
        } else {
            return (false);
        }
    }

    /**
     * read disk cache
     */
    public function getCache(string $mbId): mixed
    {
        if ($this->enabled) {
            try {
                if (! empty($this->cachePath)) {
                    if (file_exists($this->getCacheFilePath($mbId))) {
                        $this->logger->debug("Loading MusicBrainz disk cache", [$mbId, $this->cachePath, $this->getCacheFilePath($mbId)]);
                        return (file_get_contents($this->getCacheFilePath($mbId)));
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
        } else {
            return (false);
        }
    }
}
