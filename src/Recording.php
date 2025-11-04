<?php

namespace aportela\MusicBrainzWrapper;

class Recording extends \aportela\MusicBrainzWrapper\Entity
{
    public const GET_API_URL = "https://musicbrainz.org/ws/2/recording/%s?inc=artist-credits&fmt=%s";

    public function get(string $mbId): \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
    {
        if (! $this->getCache($mbId)) {
            $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
            $responseBody = $this->httpGET($url);
            if (! empty($responseBody)) {
                $this->saveCache($mbId, $responseBody);
                return ($this->parse($responseBody));
            } else {
                $this->logger->error("\aportela\MusicBrainzWrapper\Recording::get - Error: empty body on API response", [$url]);
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
            }
        } else {
            if (! empty($this->raw)) {
                return ($this->parse($this->raw));
            } else {
                $this->logger->error("\aportela\MusicBrainzWrapper\Recording::get - Error: cached data for identifier is empty", [$mbId]);
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidCacheException("Cached data for identifier ({$mbId}) is empty");
            }
        }
    }

    public function parse(string $rawText): \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
    {
        $this->reset();
        switch ($this->apiFormat) {
            case \aportela\MusicBrainzWrapper\APIFormat::XML:
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Recording($rawText);
                break;
            case \aportela\MusicBrainzWrapper\APIFormat::JSON:
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Recording($rawText);
                break;
            default:
                $this->logger->error("\aportela\MusicBrainzWrapper\Recording::parse - Error: invalid API format", [$this->apiFormat]);
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("Invalid API format: {$this->apiFormat->value}");
        }
        $this->raw = $rawText;
        return ($this->parser->parse());
    }
}
