<?php

namespace aportela\MusicBrainzWrapper;

class Release extends \aportela\MusicBrainzWrapper\Entity
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/release/?query=%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=artist-credits+recordings+url-rels&fmt=%s";

    public ?string $title;
    public int $year;
    public object $artist;
    public array $tracks = [];
    public int $trackCount;
    public string $coverArtArchive;

    public function search(string $title, string $artist, string $year, int $limit = 1): array
    {
        $queryParams = [
            "release:" . urlencode($title)
        ];
        if (!empty($artist)) {
            $queryParams[] = "artistname:" . urlencode($artist);
        }
        if (!empty($year) && mb_strlen($year) == 4) {
            $queryParams[] = "date:" . urlencode($year);
        }
        $url = sprintf(self::SEARCH_API_URL, implode(urlencode(" AND "), $queryParams), $limit, $this->apiFormat);
        $response = $this->http->GET($url);
        if ($response->code == 200) {
            $results = [];
            if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML) {
                $xml = simplexml_load_string($response->body);
                if ($xml->{"release-list"} && $xml->{"release-list"}['count'] > 0) {
                    foreach ($xml->{"release-list"}->{"release"} as $release) {
                        $releaseDate = isset($release->{"date"}) && !empty($release->{"date"}) ? $release->{"date"} : "";
                        $results[] = (object) [
                            "mbId" => isset($release["id"]) ? (string) $release["id"] : null,
                            "title" => isset($release->{"title"}) ? (string) $release->{"title"} : null,
                            "year" => strlen($releaseDate) == 10 ? (string) date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y') : (strlen($releaseDate) == 4 ? ((string) $release->{"date"}) : null),
                            "artist" => (object) [
                                "mbId" => isset($release->{"artist-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}->artist) ? (string) $release->{"artist-credit"}->{"name-credit"}->artist["id"] : null,
                                "name" => isset($release->{"artist-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}->artist) ? (string) $release->{"artist-credit"}->{"name-credit"}->artist->name : null
                            ],
                            "trackCount" => isset($release->{"medium-list"}) && isset($release->{"medium-list"}["count"]) && $release->{"medium-list"}["count"] > 0 ? (int) $release->{"medium-list"}->{"track-count"} : 0
                        ];
                    }
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($title, $response->code);
                }
                return ($results);
            } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON) {
                $json = json_decode($response->body);
                if ($json->{"count"} > 0 && is_array($json->{"releases"}) && count($json->{"releases"}) > 0) {
                    foreach ($json->{"releases"} as $release) {
                        $releaseDate = isset($release->{"date"}) && !empty($release->{"date"}) ? $release->{"date"} : "";
                        $results[] = (object) [
                            "mbId" => isset($release->{"id"}) ? (string) $release->{"id"} : null,
                            "title" => isset($release->{"title"}) ? (string) $release->{"title"} : null,
                            "year" => strlen($releaseDate) == 10 ? (string) date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y') : (strlen($releaseDate) == 4 ? ((string) $release->{"date"}) : null),
                            "artist" => (object) [
                                "mbId" => isset($release->{"artist-credit"}) && is_array($release->{"artist-credit"}) && count($release->{"artist-credit"}) > 0 ? $release->{"artist-credit"}[0]->artist->id : null,
                                "name" => isset($release->{"artist-credit"}) && is_array($release->{"artist-credit"}) && count($release->{"artist-credit"}) > 0 ? $release->{"artist-credit"}[0]->artist->name : null
                            ],
                            "trackCount" => isset($release->{"track-count"}) ? (int) $release->{"track-count"} : 0
                        ];
                    }
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($title, $response->code);
                }
                return ($results);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
            }
        } else if ($response->code == 503) {
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($title, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($title, $response->code);
        }
    }

    public function get(string $mbId): void
    {
        $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat);
        $response = $this->http->GET($url);
        if ($response->code == 200) {
            $this->parse($response->body);
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

    public function parse(string $rawText): void
    {
        $this->mbId = null;
        $this->raw = $rawText;
        $this->title = null;
        $this->artist = (object) ['mbId' => null, 'name' => null];
        $this->tracks = [];
        $this->trackCount = 0;
        $this->coverArtArchive = (object) ['front' => false, 'back' => false];
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML) {
            $xml = simplexml_load_string($this->raw);
            $this->mbId = isset($xml->{"release"}->attributes()->{"id"}) ? (string) $xml->{"release"}->attributes()->{"id"} : null;
            $this->title = isset($xml->{"release"}->{"title"}) ? (string) $xml->{"release"}->{"title"} : null;
            $releaseDate = isset($xml->{"release"}->{"date"}) && !empty($xml->{"release"}->{"date"}) ? $xml->{"release"}->{"date"} : "";
            $this->year = strlen($releaseDate) == 10 ? (string) date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y') : (strlen($releaseDate) == 4 ? ((string) $releaseDate) : null);
            $this->artist->mbId = isset($xml->{"release"}->{"artist-credit"}) && isset($xml->{"release"}->{"artist-credit"}->{"name-credit"}) && isset($xml->{"release"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $xml->{"release"}->{"artist-credit"}->{"name-credit"}->{"artist"}["id"] : null;
            $this->artist->name = isset($xml->{"release"}->{"artist-credit"}) && isset($xml->{"release"}->{"artist-credit"}->{"name-credit"}) && isset($xml->{"release"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $xml->{"release"}->{"artist-credit"}->{"name-credit"}->{"artist"}->{"name"} : null;
            $this->coverArtArchive = (object) [
                'front' => isset($xml->{"release"}->{"cover-art-archive"}) && isset($xml->{"release"}->{"cover-art-archive"}->{"front"}) ? $xml->{"release"}->{"cover-art-archive"}->{"front"} == "true" : false,
                'back' => isset($xml->{"release"}->{"cover-art-archive"}) && isset($xml->{"release"}->{"cover-art-archive"}->{"back"}) ? $xml->{"release"}->{"cover-art-archive"}->{"back"} == "true" : false
            ];
            if (isset($xml->{"release"}->{"medium-list"}) && isset($xml->{"release"}->{"medium-list"}["count"]) && $xml->{"release"}->{"medium-list"}["count"] > 0) {
                $this->trackCount = isset($xml->{"release"}->{"medium-list"}->{"medium"}->{"track-list"}) && isset($xml->{"release"}->{"medium-list"}->{"medium"}->{"track-list"}["count"]) ? (int) $xml->{"release"}->{"medium-list"}->{"medium"}->{"track-list"}["count"] : 0;
                if ($this->trackCount > 0) {
                    foreach ($xml->{"release"}->{"medium-list"}->{"medium"}->{"track-list"}->track as $track) {
                        $this->tracks[] = (object) [
                            "mbId" => isset($track["id"]) ? (string) $track["id"] : null,
                            "number" => isset($track->{"number"}) ? (int) $track->{"number"} : null,
                            "length" => isset($track->{"length"}) ? (int) $track->{"length"} : null,
                            "title" => isset($track->{"recording"}) && isset($track->{"recording"}->{"title"}) ? (string) $track->{"recording"}->{"title"} : null,
                            "artist" => [
                                "mbId" => isset($track->{"recording"}) && isset($track->{"recording"}->{"artist-credit"}) && isset($track->{"recording"}->{"artist-credit"}->{"name-credit"}) && isset($track->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $track->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}["id"] : null,
                                "name" => isset($track->{"recording"}) && isset($track->{"recording"}->{"artist-credit"}) && isset($track->{"recording"}->{"artist-credit"}->{"name-credit"}) && isset($track->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $track->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}->{"name"} : null
                            ]
                        ];
                    }
                }
            }
        } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON) {
            $json = json_decode($this->raw);
            $this->mbId = isset($json->{"id"}) ? (string) $json->{"id"} : null;
            $this->title = isset($json->{"title"}) ? (string) $json->{"title"} : null;
            $releaseDate = isset($json->{"date"}) && !empty($json->{"date"}) ? $json->{"date"} : "";
            $this->year = strlen($releaseDate) == 10 ? (string) date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y') : (strlen($releaseDate) == 4 ? ((string) $releaseDate) : null);
            $this->artist->mbId = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->id : null;
            $this->artist->name = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->name : null;
            $this->coverArtArchive = (object) [
                'front' => isset($json->{"cover-art-archive"}) && isset($json->{"cover-art-archive"}->front) ? (bool) $json->{"cover-art-archive"}->front : false,
                'back' => isset($json->{"cover-art-archive"}) && isset($json->{"cover-art-archive"}->back) ? (bool) $json->{"cover-art-archive"}->back : false
            ];
            if (isset($json->{"media"}) && is_array($json->{"media"}) && count($json->{"media"}) > 0 && isset($json->{"media"}[0]->tracks) && is_array($json->{"media"}[0]->tracks) && count($json->{"media"}[0]->tracks) > 0) {
                $this->trackCount = isset($json->{"media"}[0]->{"track-count"}) ? (int) $json->{"media"}[0]->{"track-count"} : 0;
                if ($this->trackCount > 0) {
                    foreach ($json->{"media"}[0]->tracks as $track) {
                        $this->tracks[] = (object) [
                            "mbId" => isset($track->{"id"}) ? (string) $track->{"id"} : null,
                            "number" => isset($track->{"number"}) ? (int) $track->{"number"} : null,
                            "length" => isset($track->{"length"}) ? (int) $track->{"length"} : null,
                            "title" => isset($track->{"title"}) ? (string) $track->{"title"} : null,
                            "artist" => [
                                "mbId" => isset($track->{"artist-credit"}) && is_array($track->{"artist-credit"}) && count($track->{"artist-credit"}) > 0 ? $track->{"artist-credit"}[0]->artist->id : null,
                                "name" => isset($track->{"artist-credit"}) && is_array($track->{"artist-credit"}) && count($track->{"artist-credit"}) > 0 ? $track->{"artist-credit"}[0]->artist->name : null
                            ]
                        ];
                    }
                }
            }
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }
}
