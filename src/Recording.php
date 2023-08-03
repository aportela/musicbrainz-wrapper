<?php

namespace aportela\MusicBrainzWrapper;

class Recording extends \aportela\MusicBrainzWrapper\Entity
{
    const GET_API_URL = "https://musicbrainz.org/ws/2/recording/%s?inc=artist-credits&fmt=%s";

    public ?string $title;
    public object $artist;

    public function get(string $mbId): void
    {
        $this->raw = null;
        $this->title = null;
        $this->artist = (object) ['mbId' => null, 'name' => null];
        $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
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
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $xml = simplexml_load_string($this->raw);
            $this->mbId = isset($xml->{"recording"}->attributes()->{"title"}) ? (string) $xml->{"recording"}->attributes()->{"title"} : null;
            $this->title = isset($xml->{"recording"}->{"title"}) ? (string) $xml->{"recording"}->{"title"} : null;
            $this->artist->mbId = isset($xml->{"recording"}->{"artist-credit"}) && isset($xml->{"recording"}->{"artist-credit"}->{"name-credit"}) && isset($xml->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $xml->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}["id"] : null;
            $this->artist->name = isset($xml->{"recording"}->{"artist-credit"}) && isset($xml->{"recording"}->{"artist-credit"}->{"name-credit"}) && isset($xml->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $xml->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}->{"name"} : null;
        } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $json = json_decode($this->raw);
            $this->mbId = isset($json->{"id"}) ? (string) $json->{"id"} : null;
            $this->title = isset($json->{"title"}) ? (string) $json->{"title"} : null;
            $this->artist->mbId = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->id : null;
            $this->artist->name = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->name : null;
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }
}
