<?php

namespace aportela\MusicBrainzWrapper;

class Artist extends \aportela\MusicBrainzWrapper\Entity
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=genres+recordings+releases+release-groups+works+url-rels&fmt=%s";

    /**
     * This is a Special Purpose Artist that should only be used if no artist of discographic relevance has been attributed to a piece of work.
     * https://musicbrainz.org/artist/eec63d3c-3b81-4ad4-b1e4-7c147d4d2b61
     */
    public const NO_ARTIST_MB_ID = "eec63d3c-3b81-4ad4-b1e4-7c147d4d2b61";

    public \aportela\MusicBrainzWrapper\ArtistType $type = \aportela\MusicBrainzWrapper\ArtistType::NONE;

    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper>
     */
    public function search(string $name, int $limit = 1): array
    {
        $this->checkThrottle();
        $url = sprintf(self::SEARCH_API_URL, urlencode($name), $limit, $this->apiFormat->value);
        $response = $this->httpGET($url);
        if ($response->code == 200) {
            $this->resetThrottle();
            if (! empty($response->body)) {
                if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
                    $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Search\Artist($response->body);
                } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
                    $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search\Artist($response->body);
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
                }
                $results = $this->parser->parse();
                if (count($results) > 0) {
                    return ($results);
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException("artist name: {$name}", 0);
                }
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIResponse("empty body");
            }
        } elseif ($response->code == 503) {
            $this->incrementThrottle();
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($name, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($name, $response->code);
        }
    }

    public function get(string $mbId): \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
    {
        if (! $this->getCache($mbId)) {
            $this->checkThrottle();
            $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
            $response = $this->httpGET($url);
            if ($response->code == 200) {
                $this->resetThrottle();
                if (! empty($response->body)) {
                    $this->saveCache($mbId, $response->body);
                    return ($this->parse($response->body));
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException("body", $response->code);
                }
            } elseif ($response->code == 400) {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($mbId, $response->code);
            } elseif ($response->code == 404) {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($mbId, $response->code);
            } elseif ($response->code == 503) {
                $this->incrementThrottle();
                throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($mbId, $response->code);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($mbId, $response->code);
            }
        } else {
            if (! empty($this->raw)) {
                return ($this->parse($this->raw));
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException("raw");
            }
        }
    }

    public function parse(string $rawText): \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Artist($rawText);
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Artist($rawText);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
        $this->raw = $rawText;
        return ($this->parser->parse());
    }
}
