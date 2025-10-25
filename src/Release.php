<?php

namespace aportela\MusicBrainzWrapper;

class Release extends \aportela\MusicBrainzWrapper\Entity
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/release/?query=%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=artist-credits+recordings+url-rels&fmt=%s";

    public ?string $title = null;
    public ?int $year = null;
    public object $artist;

    /**
     * @var array<mixed>
     */
    public array $media = [];
    public object $coverArtArchive;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = 0, ?string $cachePath = null)
    {
        parent::__construct($logger, $apiFormat, $throttleDelayMS, $cachePath);
        $this->reset();
    }

    protected function reset(): void
    {
        parent::reset();
        $this->title = null;
        $this->year = null;
        $this->artist = (object) ["mbId" => null, "name" => null];
        $this->media = [];
        $this->coverArtArchive = (object) ["front" => false, "back" => false];
    }

    /**
     * @return array<mixed>
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
        $response = $this->http->GET($url);
        if ($response->code == 200) {
            $results = [];
            if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
                $xml = $this->parseXML($response->body);
                if ($xml->{"release-list"} && isset($xml->{"release-list"}['count']) && intval($xml->{"release-list"}['count']) > 0) {
                    foreach ($xml->{"release-list"}->{"release"} as $release) {
                        $releaseDate = isset($release->{"date"}) && !empty($release->{"date"}) ? $release->{"date"} : "";
                        $results[] = (object) [
                            "mbId" => isset($release["id"]) ? (string) $release["id"] : null,
                            "title" => isset($release->{"title"}) ? (string) $release->{"title"} : null,
                            "year" => strlen($releaseDate) == 10 ? intval(date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y')) : (strlen($releaseDate) == 4 ? intval($release->{"date"}) : null),
                            "artist" => (object) [
                                "mbId" => isset($release->{"artist-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}->artist) ? (string) $release->{"artist-credit"}->{"name-credit"}->artist["id"] : null,
                                "name" => isset($release->{"artist-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}->artist) ? (string) $release->{"artist-credit"}->{"name-credit"}->artist->name : null
                            ]
                        ];
                    }
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($title, $response->code);
                }
                return ($results);
            } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
                $json = $this->parseJSON($response->body);
                if ($json->{"count"} > 0 && is_array($json->{"releases"}) && count($json->{"releases"}) > 0) {
                    foreach ($json->{"releases"} as $release) {
                        $releaseDate = isset($release->{"date"}) && !empty($release->{"date"}) ? $release->{"date"} : "";
                        $results[] = (object) [
                            "mbId" => isset($release->{"id"}) ? (string) $release->{"id"} : null,
                            "title" => isset($release->{"title"}) ? (string) $release->{"title"} : null,
                            "year" => strlen($releaseDate) == 10 ? intval(date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y')) : (strlen($releaseDate) == 4 ? intval($release->{"date"}) : null),
                            "artist" => (object) [
                                "mbId" => isset($release->{"artist-credit"}) && is_array($release->{"artist-credit"}) && count($release->{"artist-credit"}) > 0 ? $release->{"artist-credit"}[0]->artist->id : null,
                                "name" => isset($release->{"artist-credit"}) && is_array($release->{"artist-credit"}) && count($release->{"artist-credit"}) > 0 ? $release->{"artist-credit"}[0]->artist->name : null
                            ]
                        ];
                    }
                } else {
                    throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($title, $response->code);
                }
                return ($results);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
            }
        } elseif ($response->code == 503) {
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($title, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($title, $response->code);
        }
    }

    public function get(string $mbId): void
    {
        if (! $this->getCache($mbId)) {
            $this->checkThrottle();
            $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
            $response = $this->http->GET($url);
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
            $xml = $this->parseXML($rawText);
            $this->mbId = isset($xml->{"release"}->attributes()->{"id"}) ? (string) $xml->{"release"}->attributes()->{"id"} : null;
            $this->title = isset($xml->{"release"}->{"title"}) ? (string) $xml->{"release"}->{"title"} : null;
            $releaseDate = isset($xml->{"release"}->{"date"}) && !empty($xml->{"release"}->{"date"}) ? $xml->{"release"}->{"date"} : "";
            $this->year = strlen($releaseDate) == 10 ? intval(date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y')) : (strlen($releaseDate) == 4 ? intval($releaseDate) : null);
            $this->artist->mbId = isset($xml->{"release"}->{"artist-credit"}) && isset($xml->{"release"}->{"artist-credit"}->{"name-credit"}) && isset($xml->{"release"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $xml->{"release"}->{"artist-credit"}->{"name-credit"}->{"artist"}["id"] : null;
            $this->artist->name = isset($xml->{"release"}->{"artist-credit"}) && isset($xml->{"release"}->{"artist-credit"}->{"name-credit"}) && isset($xml->{"release"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $xml->{"release"}->{"artist-credit"}->{"name-credit"}->{"artist"}->{"name"} : null;
            $this->coverArtArchive = (object) [
                'front' => isset($xml->{"release"}->{"cover-art-archive"}) && isset($xml->{"release"}->{"cover-art-archive"}->{"front"}) ? $xml->{"release"}->{"cover-art-archive"}->{"front"} == "true" : false,
                'back' => isset($xml->{"release"}->{"cover-art-archive"}) && isset($xml->{"release"}->{"cover-art-archive"}->{"back"}) ? $xml->{"release"}->{"cover-art-archive"}->{"back"} == "true" : false
            ];
            if (isset($xml->{"release"}->{"medium-list"}) && isset($xml->{"release"}->{"medium-list"}["count"]) && intval($xml->{"release"}->{"medium-list"}["count"]) > 0) {
                foreach ($xml->{"release"}->{"medium-list"}->{"medium"} as $medium) {
                    $tracks = [];
                    if (intval($medium->{"track-list"}["count"]) > 0) {
                        foreach ($medium->{"track-list"}->track as $track) {
                            $tracks[] = (object) [
                                "mbId" => isset($track["id"]) ? (string) $track["id"] : null,
                                "position" => isset($track->{"position"}) ? (int) $track->{"position"} : null,
                                "length" => isset($track->{"length"}) ? (int) $track->{"length"} : null,
                                "title" => isset($track->{"recording"}) && isset($track->{"recording"}->{"title"}) ? (string) $track->{"recording"}->{"title"} : null,
                                "artist" => (object) [
                                    "mbId" => isset($track->{"recording"}) && isset($track->{"recording"}->{"artist-credit"}) && isset($track->{"recording"}->{"artist-credit"}->{"name-credit"}) && isset($track->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $track->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}["id"] : null,
                                    "name" => isset($track->{"recording"}) && isset($track->{"recording"}->{"artist-credit"}) && isset($track->{"recording"}->{"artist-credit"}->{"name-credit"}) && isset($track->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $track->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}->{"name"} : null
                                ]
                            ];
                        }
                    }
                    $this->media[] = (object) [
                        "position" => (int) $medium->{"position"},
                        "trackCount" => (int) $medium->{"track-list"}["count"],
                        "tracks" => $tracks
                    ];
                }
            }
            $this->raw = $rawText;
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $json = $this->parseJSON($rawText);
            $this->mbId = isset($json->{"id"}) ? (string) $json->{"id"} : null;
            $this->title = isset($json->{"title"}) ? (string) $json->{"title"} : null;
            $releaseDate = isset($json->{"date"}) && !empty($json->{"date"}) ? $json->{"date"} : "";
            $this->year = strlen($releaseDate) == 10 ? intval(date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y')) : (strlen($releaseDate) == 4 ? intval($releaseDate) : null);
            $this->artist->mbId = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->id : null;
            $this->artist->name = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->name : null;
            $this->coverArtArchive = (object) [
                'front' => isset($json->{"cover-art-archive"}) && isset($json->{"cover-art-archive"}->front) ? (bool) $json->{"cover-art-archive"}->front : false,
                'back' => isset($json->{"cover-art-archive"}) && isset($json->{"cover-art-archive"}->back) ? (bool) $json->{"cover-art-archive"}->back : false
            ];
            if (isset($json->{"media"}) && is_array($json->{"media"}) && count($json->{"media"}) > 0) {
                foreach ($json->{"media"} as $media) {
                    $tracks = [];
                    if ($media->{"track-count"} > 0) {
                        foreach ($media->{"tracks"} as $track) {
                            $tracks[] = (object) [
                                "mbId" => isset($track->{"id"}) ? (string) $track->{"id"} : null,
                                "position" => isset($track->{"position"}) ? (int) $track->{"position"} : null,
                                "length" => isset($track->{"length"}) ? (int) $track->{"length"} : null,
                                "title" => isset($track->{"title"}) ? (string) $track->{"title"} : null,
                                "artist" => (object) [
                                    "mbId" => isset($track->{"artist-credit"}) && is_array($track->{"artist-credit"}) && count($track->{"artist-credit"}) > 0 ? $track->{"artist-credit"}[0]->artist->id : null,
                                    "name" => isset($track->{"artist-credit"}) && is_array($track->{"artist-credit"}) && count($track->{"artist-credit"}) > 0 ? $track->{"artist-credit"}[0]->artist->name : null
                                ]
                            ];
                        }
                    }
                    $this->media[] = (object) [
                        "position" => (int) $media->{"position"},
                        "trackCount" => (int) $media->{"track-count"},
                        "tracks" => $tracks
                    ];
                }
            }
            $this->raw = $rawText;
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }
}
