<?php

namespace aportela\MusicBrainzWrapper;

use stdClass;

class Artist extends \aportela\MusicBrainzWrapper\Entity
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=artist:%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=genres&fmt=%s";

    public $name;
    public $country;
    public $genres;

    public function search(string $name, int $limit = 1): array
    {
        $queryParams = [
            "artist:" . urlencode($name)
        ];
        $url = sprintf(self::SEARCH_API_URL, implode(urlencode(" AND "), $queryParams), $limit, $this->apiFormat);
        $response = $this->http->GET($url);
        if ($response->code == 200) {
            if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML) {
                $xml = simplexml_load_string($response->body);
                if ($xml->{"artist-list"} && $xml->{"artist-list"}["count"] > 0 && $xml->{"artist-list"}->{"artist"}) {
                    $results = [];
                    foreach ($xml->{"artist-list"}->{"artist"} as $artist) {
                        $results[] = (object) [
                            "mbId" => isset($artist["id"]) ? (string) $artist["id"] : null,
                            "name" => isset($artist->{"name"}) ? (string) $artist->{"name"} : null,
                            "country" => isset($artist->{"country"}) ? mb_strtolower((string) $artist->{"country"}) : null
                        ];
                    }
                    return ($results);
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($name, $response->code);
                }
            } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON) {
                $json = json_decode($response->body);
                if ($json->{"count"} > 0 && is_array($json->{"artists"}) && count($json->{"artists"}) > 0) {
                    $results = [];
                    foreach ($json->{"artists"} as $artist) {
                        $results[] = (object) [
                            "mbId" => isset($artist->{"id"}) ? (string) $artist->{"id"} : null,
                            "name" => isset($artist->{"name"}) ? (string) $artist->{"name"} : null,
                            "country" => isset($artist->{"country"}) ? mb_strtolower((string) $artist->{"country"}) : null
                        ];
                    }
                    return ($results);
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($name, $response->code);
                }
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
            }
        } else if ($response->code == 503) {
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($name, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($name, $response->code);
        }
    }

    public function get(string $mbId): void
    {
        $this->raw = null;
        $this->name = null;
        $this->country = null;
        $this->genres = [];
        $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat);
        $response = $this->http->GET($url);
        if ($response->code == 200) {
            $this->mbId = $mbId;
            $this->raw = $response->body;
            if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML) {
                $xml = simplexml_load_string($this->raw);
                $this->name = isset($xml->{"artist"}->{"name"}) ? (string) $xml->{"artist"}->{"name"} : null;
                $this->country = isset($xml->{"artist"}->{"country"}) ? mb_strtolower((string) $xml->{"artist"}->{"country"}) : null;
                if (isset($xml->{"artist"}->{"genre-list"})) {
                    foreach ($xml->{"artist"}->{"genre-list"}->{"genre"} as $genre) {
                        $this->genres[] = trim(mb_strtolower((string) $genre->{"name"}));
                    }
                    $this->genres = array_unique(($this->genres));
                } else {
                    $this->genres = [];
                }
            } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON) {
                $json = json_decode($this->raw);
                $this->name = isset($json->{"name"}) ? (string) $json->{"name"} : null;
                $this->country = isset($json->{"country"}) ? mb_strtolower((string) $json->{"country"}) : null;
                if (isset($json->{"genres"})) {
                    foreach ($json->{"genres"} as $genre) {
                        $this->genres[] = trim(mb_strtolower((string) $genre->{"name"}));
                    }
                    $this->genres = array_unique(($this->genres));
                } else {
                    $this->genres = [];
                }
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
            }
        } else if ($response->code == 400) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($mbId, $response->code);
        } else if ($response->code == 404) {
            throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($mbId, $response->code);
        } else if ($response->code == 503) {
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($mbId, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($mbId, $response->code);
        }
    }
}
