<?php

namespace aportela\MusicBrainzWrapper;

class CoverArtArchive extends \aportela\MusicBrainzWrapper\Entity
{
    private const string DIRECT_IMAGE_URL = "https://coverartarchive.org/release/%s/%s-%s";
    private const string GET_API_URL = "https://coverartarchive.org/release/%s/";

    public function getReleaseImageURL(string $releaseMbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType $coverArtArchiveImageType, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize $coverArtArchiveImageSize): string
    {
        return (sprintf(self::DIRECT_IMAGE_URL, $releaseMbId, $coverArtArchiveImageType->value, $coverArtArchiveImageSize->value));
    }

    public function get(string $mbId): \aportela\MusicBrainzWrapper\ParseHelpers\CoverArtArchiveHelper
    {
        $url = sprintf(self::GET_API_URL, $mbId);
        if (! $this->getCache($mbId)) {
            $responseBody = $this->httpGET($url);
            if (!in_array($responseBody, [null, '', '0'], true)) {
                $this->saveCache($mbId, $responseBody);
                return ($this->parse($responseBody));
            } else {
                $this->logger->error("\aportela\MusicBrainzWrapper\CoverArtArchive::get - Error: empty body on API response", [$url]);
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
            }
        } elseif (!in_array($this->raw, [null, '', '0'], true)) {
            return ($this->parse($this->raw));
        } else {
            $this->logger->error("\aportela\MusicBrainzWrapper\CoverArtArchive::get - Error: cached data for identifier is empty", [$mbId]);
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidCacheException("Cached data for identifier ({$mbId}) is empty");
        }
    }

    public function parse(string $rawText): \aportela\MusicBrainzWrapper\ParseHelpers\CoverArtArchiveHelper
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\CoverArtArchive($rawText);
        } else {
            $this->logger->error("\aportela\MusicBrainzWrapper\CoverArtArchive::parse - Error: invalid API format", [$this->apiFormat]);
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("Invalid API format: {$this->apiFormat->value}");
        }
        $this->raw = $rawText;
        return ($this->parser->parse());
    }
}
