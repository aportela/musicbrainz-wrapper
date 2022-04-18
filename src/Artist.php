<?php

namespace aportela\MusicBrainzWrapper;

class Artist
{

    protected $logger;

    const XML_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=aliases";
    const JSON_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=aliases&fmt=json";

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->debug("Artist::__construct");
    }

    public function __destruct()
    {
        $this->logger->debug("Artist::__destruct");
    }

    public function GETXML(string $mbId): string
    {
        $http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger, "MusicBrainzWrapper - https://github.com/aportela/musicbrainz-wrapper (766f6964+github@gmail.com)");
        $response = $http->GET(sprintf(self::XML_API_URL, $mbId));
        if ($response->code == 200) {
            return ($response->body);
        } else {
            throw new \Exception($response->code);
        }
    }

    public function GETJSON(string $mbId): string
    {
        $http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger, "MusicBrainzWrapper - https://github.com/aportela/musicbrainz-wrapper (766f6964+github@gmail.com)");
        $response = $http->GET(sprintf(self::JSON_API_URL, $mbId));
        if ($response->code == 200) {
            return ($response->body);
        } else {
            throw new \Exception($response->code);
        }
    }
}
