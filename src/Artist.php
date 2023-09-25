<?php

namespace aportela\MusicBrainzWrapper;

class Artist extends \aportela\MusicBrainzWrapper\Entity
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=genres+recordings+releases+release-groups+works+url-rels&fmt=%s";

    public ?string $name;
    public ?string $country;
    public array $genres = [];
    public array $relations = [];

    public function search(string $name, int $limit = 1): array
    {
        $url = sprintf(self::SEARCH_API_URL, urlencode($name), $limit, $this->apiFormat->value);
        $response = $this->http->GET($url);
        if ($response->code == 200) {
            if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
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
            } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
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
        } elseif ($response->code == 503) {
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($name, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($name, $response->code);
        }
    }

    public function get(string $mbId): void
    {
        $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
        $response = $this->http->GET($url);
        if ($response->code == 200) {
            $this->parse($response->body);
        } elseif ($response->code == 400) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($mbId, $response->code);
        } elseif ($response->code == 404) {
            throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($mbId, $response->code);
        } elseif ($response->code == 503) {
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($mbId, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($mbId, $response->code);
        }
    }

    public function parse(string $rawText): void
    {
        $this->mbId = null;
        $this->raw = $rawText;
        $this->name = null;
        $this->country = null;
        $this->genres = [];
        $this->relations = [];
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $xml = simplexml_load_string($this->raw);
            $this->mbId = isset($xml->{"artist"}->attributes()->{"id"}) ? (string) $xml->{"artist"}->attributes()->{"id"} : null;
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

            if (isset($xml->{"artist"}->{"relation-list"})) {
                foreach ($xml->{"artist"}->{"relation-list"}->{"relation"} as $relation) {
                    $newRelation = new \stdClass();
                    $newRelation->typeId = (string)$relation->attributes()->{"type-id"};
                    $newRelation->name = (string)$relation->attributes()->{"type"};
                    $newRelation->url = (string)$relation->{"target"};
                    $this->relations[] = $newRelation;
                }
            } else {
                $this->relations = [];
            }
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $json = json_decode($this->raw);
            $this->mbId = isset($json->{"id"}) ? (string) $json->{"id"} : null;
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
            if (isset($json->{"relations"})) {
                foreach ($json->{"relations"} as $relation) {
                    $newRelation = new \stdClass();
                    $newRelation->typeId = (string)$relation->{"type-id"};
                    $newRelation->name = (string)$relation->{"type"};
                    $newRelation->url = (string)$relation->{"url"}->{"resource"};
                    $this->relations[] = $newRelation;
                }
            } else {
                $this->relations = [];
            }
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }

    public function getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType $typeId): array
    {
        $urls = [];
        foreach ($this->relations as $relation) {
            if ($relation->typeId == $typeId->value) {
                $urls[] = $relation->url;
            }
        }
        return ($urls);
    }
}
