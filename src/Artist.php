<?php

namespace aportela\MusicBrainzWrapper;

use stdClass;

class Artist extends \aportela\MusicBrainzWrapper\Entity
{
    const XML_SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=artist:%s&limit=%d";
    const JSON_SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=artist:%s&limit=%d&fmt=json";
    const XML_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=aliases";
    const JSON_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=aliases&fmt=json";

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

    public function get(string $mbId): string
    {
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML) {
            $response = $this->http->GET(sprintf(self::XML_API_URL, $mbId));
            if ($response->code == 200) {
                return ($response->body);
            } else if ($response->code == 400) {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($mbId, $response->code);
            } else if ($response->code == 404) {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($mbId, $response->code);
            } else {
                throw new \Exception($mbId, $response->code);
            }
        } else if ($this->apiFormat == \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON) {
            $response = $this->http->GET(sprintf(self::JSON_API_URL, $mbId));
            if ($response->code == 200) {
                return ($response->body);
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
