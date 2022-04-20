<?php

namespace aportela\MusicBrainzWrapper;

class Release extends \aportela\MusicBrainzWrapper\Entity
{
    private const XML_GET_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=artist-credits%%2Brecordings";
    private const JSON_GET_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=artist-credits%%2Brecordings&fmt=json";

    public $title;
    public $year;
    public $artist;
    public $tracks;
    public $coverArtArchive;

    public function get(string $mbId): void
    {
        $this->raw = null;
        $this->title = null;
        $this->artist = (object) [ 'mbId' => null, 'name' => null ];
        $this->tracks = [];
        $this->coverArtArchive = (object) [ 'front' => false, 'back' => false ];
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML) {
            $response = $this->http->GET(sprintf(self::XML_GET_API_URL, $mbId));
            if ($response->code == 200) {
                $this->mbId = $mbId;
                $this->raw = $response->body;
            } else if ($response->code == 400) {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($mbId, $response->code);
            } else if ($response->code == 404) {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($mbId, $response->code);
            } else if ($response->code == 503) {
                throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($mbId, $response->code);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($mbId, $response->code);
            }
        } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON) {
            $response = $this->http->GET(sprintf(self::JSON_GET_API_URL, $mbId));
            if ($response->code == 200) {
                $this->mbId = $mbId;
                $this->raw = $response->body;
                $json = json_decode($this->raw);
                $this->title = isset($json->{"title"}) ? (string) $json->{"title"}: null;
                $this->year = isset($json->{"date"}) && strlen($json->{"date"}) == 10 ? (string) date_format(date_create_from_format('Y-m-d', $json->{"date"}), 'Y'): null;
                $this->artist->mbId = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->id : null;
                $this->artist->name = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->name : null;
                $this->coverArtArchive = (object) [
                    'front' => isset($json->{"cover-art-archive"}) && isset($json->{"cover-art-archive"}->front) ? (bool) $json->{"cover-art-archive"}->front: false,
                    'back' => isset($json->{"cover-art-archive"}) && isset($json->{"cover-art-archive"}->back) ? (bool) $json->{"cover-art-archive"}->back: false
                ];
                if (isset($json->{"media"}) && is_array($json->{"media"}) && count($json->{"media"}) > 0 && isset($json->{"media"}[0]->tracks) && is_array($json->{"media"}[0]->tracks) && count($json->{"media"}[0]->tracks) > 0) {
                    foreach($json->{"media"}[0]->tracks as $track) {
                        $this->tracks[] = (object) [
                            "mbId" => isset($track->{"id"}) ? (string) $track->{"id"} : null,
                            "number" => isset($track->{"number"}) ? (int) $track->{"number"} : null,
                            "length" => isset($track->{"length"}) ? (int) $track->{"length"} : null,
                            "title" => isset($track->{"title"}) ? (string) $track->{"title"} : null,
                            "artist" => [
                                "mbId" => isset($track->{"artist-credit"}) && is_array($track->{"artist-credit"}) && count($track->{"artist-credit"}) > 0 ? $track->{"artist-credit"}[0]->artist->id: null,
                                "name" => isset($track->{"artist-credit"}) && is_array($track->{"artist-credit"}) && count($track->{"artist-credit"}) > 0 ? $track->{"artist-credit"}[0]->artist->name: null
                            ]
                        ];
                    }
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
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }
}
