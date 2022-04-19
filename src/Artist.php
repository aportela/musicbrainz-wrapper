<?php

namespace aportela\MusicBrainzWrapper;

use stdClass;

class Artist extends \aportela\MusicBrainzWrapper\Entity
{
    private const XML_SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=artist:%s&limit=%d";
    private const JSON_SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=artist:%s&limit=%d&fmt=json";
    private const XML_GET_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=aliases";
    private const JSON_GET_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=aliases&fmt=json";

    public $name;
    public $country;

    public function search(string $name, int $limit = 1): array
    {
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML) {
            $response = $this->http->GET(sprintf(self::XML_SEARCH_API_URL, urlencode($name), $limit));
            if ($response->code == 200) {
                $xml = simplexml_load_string($response->body);
                if ($xml->{"artist-list"} && $xml->{"artist-list"}['count'] > 0 && $xml->{"artist-list"}->{"artist"}) {
                    $results = [];
                    foreach ($xml->{"artist-list"}->{"artist"} as $artist) {
                        $results[] = (object) [
                            "mbId" => isset($artist["id"]) ? (string) $artist["id"] : null,
                            "name" => isset($artist->{"name"}) ? (string) $artist->{"name"} : null,
                            "country" => isset($artist->{"country"}) ? (string) $artist->{"country"} : null
                        ];
                    }
                    return ($results);
                } else {
                    return ([]);
                }
            } else {
                return ([]);
            }
        } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON) {
            $response = $this->http->GET(sprintf(self::JSON_SEARCH_API_URL, urlencode($name), $limit));
            if ($response->code == 200) {
                $json = json_decode($response->body);
                if ($json->{"count"} > 0 && is_array($json->{"artists"}) && count($json->{"artists"}) > 0) {
                    $results = [];
                    foreach ($json->{"artists"} as $artist) {
                        $results[] = (object) [
                            "mbId" => isset($artist->{"id"}) ? (string) $artist->{"id"} : null,
                            "name" => isset($artist->{"name"}) ? (string) $artist->{"name"} : null,
                            "country" => isset($artist->{"country"}) ? (string) $artist->{"country"} : null
                        ];
                    }
                    return ($results);
                } else {
                    return ([]);
                }
            } else {
                return ([]);
            }
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }

    public function get(string $mbId): void
    {
        $this->raw = null;
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML) {
            $response = $this->http->GET(sprintf(self::XML_GET_API_URL, $mbId));
            if ($response->code == 200) {
                $this->mbId = $mbId;
                $this->raw = $response->body;
                $xml = simplexml_load_string($this->raw);
                $this->name = isset($xml->{"artist"}->{"name"}) ? (string) $xml->{"artist"}->{"name"}: null;
                $this->country = isset($xml->{"artist"}->{"country"}) ? (string) $xml->{"artist"}->{"country"}: null;
            } else if ($response->code == 400) {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($mbId, $response->code);
            } else if ($response->code == 404) {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($mbId, $response->code);
            } else {
                throw new \Exception($mbId, $response->code);
            }
        } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON) {
            $response = $this->http->GET(sprintf(self::JSON_GET_API_URL, $mbId));
            if ($response->code == 200) {
                $this->mbId = $mbId;
                $this->raw = $response->body;
                $json = json_decode($this->raw);
                $this->name = isset($json->{"name"}) ? (string) $json->{"name"}: null;
                $this->country = isset($json->{"country"}) ? (string) $json->{"country"}: null;
            } else if ($response->code == 400) {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($mbId, $response->code);
            } else if ($response->code == 404) {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($mbId, $response->code);
            } else {
                throw new \Exception($mbId, $response->code);
            }
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }
}
