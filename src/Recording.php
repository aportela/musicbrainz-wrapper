<?php

namespace aportela\MusicBrainzWrapper;

class Recording extends \aportela\MusicBrainzWrapper\Entity
{
    const XML_API_URL = "https://musicbrainz.org/ws/2/recording/%s?inc=aliases%%2Bartist-credits%%2Breleases";
    const JSON_API_URL = "https://musicbrainz.org/ws/2/recording/%s?inc=aliases%%2Bartist-credits%%2Breleases&fmt=json";

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
