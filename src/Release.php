<?php

namespace aportela\MusicBrainzWrapper;

class Release extends \aportela\MusicBrainzWrapper\Entity
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/release/?query=%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=artist-credits+recordings+url-rels&fmt=%s";

    public ?string $title;
    public ?int $year = null;
    /**
     * @var array<mixed>
     */
    public array $artistCredit = [];

    public object $coverArtArchive;

    /**
     * @var array<mixed>
     */
    public array $media = [];

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
        $this->artistCredit = [];
        $this->coverArtArchive = (object) ["front" => false, "back" => false];
        $this->media = [];
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
        $response = $this->httpGET($url);
        if ($response->code == 200) {
            $this->resetThrottle();
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
            $this->incrementThrottle();
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
            $response = $this->httpGET($url);
            if ($response->code == 200) {
                $this->resetThrottle();
                $this->saveCache($mbId, $response->body);
                $this->parse($response->body);
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
            $this->parse($this->raw);
        }
    }

    public function parse(string $rawText): void
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Release($rawText);
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Release($rawText);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
        $data = $this->parser->parse();
        $this->mbId = $data->mbId;
        $this->title = $data->title;
        $this->year = $data->year;
        $this->artistCredit = $data->artistCredit;
        $this->coverArtArchive = $data->coverArtArchive;
        $this->media = $data->media;
        $this->raw = $rawText;
    }
}
