<?php

namespace aportela\MusicBrainzWrapper;

use stdClass;

class Artist
{

    protected $logger;
    protected $http;

    const XML_SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=artist:%s&limit=%d";
    const JSON_SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=artist:%s&limit=%d&fmt=json";
    const XML_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=aliases";
    const JSON_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=aliases&fmt=json";

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->debug("Artist::__construct");
        $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger, \aportela\MusicBrainzWrapper\MusicBrainz::USER_AGENT);
    }

    public function __destruct()
    {
        $this->logger->debug("Artist::__destruct");
    }

    public function SEARCHXML(string $name, int $limit = 1): \stdClass
    {
        $response = $this->http->GET(sprintf(self::XML_SEARCH_API_URL, urlencode($name), $limit));
        if ($response->code == 200) {
            $xml = simplexml_load_string($response->body);
            if ($xml->{"artist-list"} && $xml->{"artist-list"}['count'] > 0 && $xml->{"artist-list"}->{"artist"}) {
                return ((object) [
                    "mbId" => (string) $xml->{"artist-list"}->{"artist"}["id"],
                    "name" => (string) $xml->{"artist-list"}->{"artist"}->{"name"}[0],
                    "country" => (string) $xml->{"artist-list"}->{"artist"}->{"country"}[0]
                ]);
            } else {
                return (null);
            }
        } else {
            return (null);
        }
    }

    public function SEARCHJSON(string $name, int $limit = 1): \stdClass
    {
        $response = $this->http->GET(sprintf(self::JSON_SEARCH_API_URL, urlencode($name), $limit));
        if ($response->code == 200) {
            $json = json_decode($response->body);
            if ($json->{"count"} > 0 && is_array($json->{"artists"}) && count($json->{"artists"}) > 0) {
                return ((object) [
                    "mbId" => (string) $json->{"artists"}[0]->{"id"},
                    "name" => (string) $json->{"artists"}[0]->{"name"},
                    "country" => (string) $json->{"artists"}[0]->{"country"},
                ]);
            } else {
                return (null);
            }
        } else {
            return (null);
        }
    }

    public function GETXML(string $mbId): string
    {
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
    }

    public function GETJSON(string $mbId): string
    {
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
    }
}
