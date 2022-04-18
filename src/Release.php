<?php

namespace aportela\MusicBrainzWrapper;

class Release
{

    protected $logger;

    const XML_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=aliases%%2Bartist-credits%%2Blabels%%2Bdiscids%%2Brecordings";
    const JSON_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=aliases%%2Bartist-credits%%2Blabels%%2Bdiscids%%2Brecordings&fmt=json";

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->debug("Release::__construct");
    }

    public function __destruct()
    {
        $this->logger->debug("Release::__destruct");
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
