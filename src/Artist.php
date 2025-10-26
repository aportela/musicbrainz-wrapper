<?php

namespace aportela\MusicBrainzWrapper;

class Artist extends \aportela\MusicBrainzWrapper\ArtistBase
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=genres+recordings+releases+release-groups+works+url-rels&fmt=%s";

    /**
     * This is a Special Purpose Artist that should only be used if no artist of discographic relevance has been attributed to a piece of work.
     * https://musicbrainz.org/artist/eec63d3c-3b81-4ad4-b1e4-7c147d4d2b61
     */
    public const NO_ARTIST_MB_ID = "eec63d3c-3b81-4ad4-b1e4-7c147d4d2b61";

    public ?string $country = null;
    /**
     * @var array<string>
     */
    public array $genres = [];
    /**
     * @var array<mixed>
     */
    public array $relations = [];

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = 0, ?string $cachePath = null)
    {
        parent::__construct($logger, $apiFormat, $throttleDelayMS, $cachePath);
        $this->reset();
    }

    protected function reset(): void
    {
        parent::reset();
        $this->country = null;
        $this->genres = [];
        $this->relations = [];
    }

    /**
     * @return array<mixed>
     */
    public function search(string $name, int $limit = 1): array
    {
        $this->checkThrottle();
        $url = sprintf(self::SEARCH_API_URL, urlencode($name), $limit, $this->apiFormat->value);
        $response = $this->httpGET($url);
        if ($response->code == 200) {
            if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
                $xml = $this->parseXML($response->body);
                if ($xml->{"artist-list"} && intval($xml->{"artist-list"}["count"]) > 0 && $xml->{"artist-list"}->{"artist"}) {
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
                $json = $this->parseJSON($response->body);
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
        if (! $this->getCache($mbId)) {
            $this->checkThrottle();
            $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
            $response = $this->httpGET($url);
            if ($response->code == 200) {
                $this->saveCache($mbId, $response->body);
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
        } else {
            $this->parse($this->raw);
        }
    }

    public function parse(string $rawText): void
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $xmlHelper = new \aportela\MusicBrainzWrapper\Helpers\XMLHelper($rawText);
            $artistXPath = $xmlHelper->getXPath("//" . $xmlHelper->getNS() . ":artist");
            if ($artistXPath === false || count($artistXPath) != 1) {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist xpath not found");
            }
            $this->mbId = $artistXPath[0]->attributes()->id ?: null;
            $this->type = \aportela\MusicBrainzWrapper\ArtistType::fromString($artistXPath[0]->attributes()->type) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE;
            $this->name = current($artistXPath[0]->children()->name) ?: null;
            $this->country = !empty($country = current($artistXPath[0]->children()->country)) ? mb_strtolower($country) : null;
            $genreList = $artistXPath[0]->children()->{"genre-list"};
            if ($genreList !== false && count($genreList) > 0) {
                $genres = $genreList->children();
                if (! empty($genres)) {
                    foreach ($genres as $genre) {
                        $this->genres[] = mb_strtolower(trim($genre->children()->name));
                    }
                    $this->genres = array_unique($this->genres);
                }
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist genre-list children not found");
            }
            $relationList = $artistXPath[0]->children()->{"relation-list"};
            if ($relationList !== false && count($relationList) > 0) {
                $relations = $relationList->children();
                if (! empty($relations)) {
                    foreach ($relations as $relation) {
                        $this->relations[] = (object) [
                            "typeId" => (string) $relation->attributes()->{"type-id"},
                            "name" => (string) $relation->attributes()->{"type"},
                            "url" => (string) $relation->{"target"}
                        ];
                    }
                }
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist relation-list children not found");
            }
            $this->raw = $rawText;
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $json = $this->parseJSON($rawText);
            $this->mbId = isset($json->{"id"}) ? (string) $json->{"id"} : null;
            $this->name = isset($json->{"name"}) ? (string) $json->{"name"} : null;
            $this->country = isset($json->{"country"}) ? mb_strtolower((string) $json->{"country"}) : null;
            if (isset($json->{"genres"})) {
                foreach ($json->{"genres"} as $genre) {
                    $this->genres[] = trim(mb_strtolower((string) $genre->{"name"}));
                }
                $this->genres = array_unique($this->genres);
            }
            if (isset($json->{"relations"})) {
                foreach ($json->{"relations"} as $relation) {
                    $this->relations[] = (object) [
                        "typeId" => (string)$relation->{"type-id"},
                        "name" => (string)$relation->{"type"},
                        "url" => (string)$relation->{"url"}->{"resource"}
                    ];
                }
            }
            $this->raw = $rawText;
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }

    /**
     * @return array<string>
     */
    public function getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType $typeId): array
    {
        return array_map(
            fn($relation) => $relation->url,
            array_values(
                array_filter(
                    $this->relations,
                    fn($relation) => $relation->typeId == $typeId->value
                )
            )
        );
    }
}
