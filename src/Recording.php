<?php

namespace aportela\MusicBrainzWrapper;

class Recording extends \aportela\MusicBrainzWrapper\Entity
{
    public const GET_API_URL = "https://musicbrainz.org/ws/2/recording/%s?inc=artist-credits&fmt=%s";

    public function get(string $mbId): \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
    {
        if (! $this->getCache($mbId)) {
            $this->checkThrottle();
            $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
            $response = $this->httpGET($url);
            if ($response->code == 200) {
                $this->resetThrottle();
                $this->saveCache($mbId, $response->body);
                return ($this->parse($response->body));
            } elseif ($response->code == 400) {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidIdentifierException($mbId, $response->code);
            } elseif ($response->code == 404) {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($mbId, $response->code);
            } elseif ($response->code == 503) {
                $this->incrementThrottle();
                throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($mbId, $response->code);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($mbId, $response->code);
            }
        } else {
            return ($this->parse($this->raw));
        }
    }

    public function parse(string $rawText): \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Recording($rawText);
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Recording($rawText);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
        $this->raw = $rawText;
        return ($this->parser->parse());
    }
}
