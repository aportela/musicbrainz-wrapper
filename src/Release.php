<?php

namespace aportela\MusicBrainzWrapper;

class Release extends \aportela\MusicBrainzWrapper\Entity
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/release/?query=%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=artist-credits+recordings+url-rels&fmt=%s";

    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper>
     */
    public function search(string $title, string $artist, string $year, int $limit = 1): array
    {
        $this->checkThrottle();
        $queryParams = [
            "release:" . urlencode($title)
        ];
        if (!empty($artist)) {
            $queryParams[] = "artistname:" . urlencode($artist);
        }
        if (!empty($year) && mb_strlen($year) == 4) {
            $queryParams[] = "date:" . urlencode($year);
        }
        $url = sprintf(self::SEARCH_API_URL, implode(urlencode(" AND "), $queryParams), $limit, $this->apiFormat->value);
        $response = $this->httpGET($url);
        if ($response->code == 200) {
            $this->resetThrottle();
            $results = [];
            if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Search\Release($response->body);
            } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search\Release($response->body);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
            }
            $results = $this->parser->parse();
            if (count($results) > 0) {
                return ($results);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException("title: {$title} - artist: {$artist} - year: {$year}", 0);
            }
        } elseif ($response->code == 503) {
            $this->incrementThrottle();
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($title, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($title, $response->code);
        }
    }

    public function get(string $mbId): \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
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

    public function parse(string $rawText): \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Release($rawText);
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Release($rawText);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
        $this->raw = $rawText;
        return ($this->parser->parse());
    }
}
