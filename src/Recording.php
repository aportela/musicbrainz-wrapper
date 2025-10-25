<?php

namespace aportela\MusicBrainzWrapper;

class Recording extends \aportela\MusicBrainzWrapper\Entity
{
    public const GET_API_URL = "https://musicbrainz.org/ws/2/recording/%s?inc=artist-credits&fmt=%s";

    public ?string $title = null;
    public object $artist;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = 0, ?string $cachePath = null)
    {
        parent::__construct($logger, $apiFormat, $throttleDelayMS, $cachePath);
        $this->reset();
    }

    protected function reset(): void
    {
        parent::reset();
        $this->title = null;
        $this->artist = (object) ["mbId" => null, "name" => null];
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
            $xml = simplexml_load_string($rawText);
            $this->mbId = isset($xml->{"recording"}->attributes()->{"title"}) ? (string) $xml->{"recording"}->attributes()->{"title"} : null;
            $this->title = isset($xml->{"recording"}->{"title"}) ? (string) $xml->{"recording"}->{"title"} : null;
            $this->artist->mbId = isset($xml->{"recording"}->{"artist-credit"}) && isset($xml->{"recording"}->{"artist-credit"}->{"name-credit"}) && isset($xml->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $xml->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}["id"] : null;
            $this->artist->name = isset($xml->{"recording"}->{"artist-credit"}) && isset($xml->{"recording"}->{"artist-credit"}->{"name-credit"}) && isset($xml->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}) ? (string) $xml->{"recording"}->{"artist-credit"}->{"name-credit"}->{"artist"}->{"name"} : null;
            $this->raw = $rawText;
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $json = json_decode($rawText);
            $this->mbId = isset($json->{"id"}) ? (string) $json->{"id"} : null;
            $this->title = isset($json->{"title"}) ? (string) $json->{"title"} : null;
            $this->artist->mbId = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->id : null;
            $this->artist->name = isset($json->{"artist-credit"}) && is_array($json->{"artist-credit"}) && count($json->{"artist-credit"}) > 0 ? $json->{"artist-credit"}[0]->artist->name : null;
            $this->raw = $rawText;
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }
}
