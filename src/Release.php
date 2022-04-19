<?php

namespace aportela\MusicBrainzWrapper;

class Release extends \aportela\MusicBrainzWrapper\Entity
{
    const XML_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=aliases%%2Bartist-credits%%2Blabels%%2Bdiscids%%2Brecordings";
    const JSON_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=aliases%%2Bartist-credits%%2Blabels%%2Bdiscids%%2Brecordings&fmt=json";

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
