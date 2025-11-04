<?php

namespace aportela\MusicBrainzWrapper;

class CoverArtArchive extends \aportela\MusicBrainzWrapper\Entity
{
    private const DIRECT_IMAGE_URL = "https://coverartarchive.org/release/%s/%s-%s";
    private const GET_API_URL = "https://coverartarchive.org/release/%s/";

    public function getReleaseImageURL(string $releaseMbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType $imageType, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize $imageSize): string
    {
        return (sprintf(self::DIRECT_IMAGE_URL, $releaseMbId, $imageType->value, $imageSize->value));
    }

    public function get(string $mbId): \aportela\MusicBrainzWrapper\ParseHelpers\CoverArtArchiveHelper
    {
        $this->checkThrottle();
        $url = sprintf(self::GET_API_URL, $mbId);
        $response = $this->httpGET($url);
        if ($response->code == 200) {
            $this->resetThrottle();
            if (! empty($response->body)) {
                return ($this->parse($response->body));
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIResponse("empty body");
            }
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
    }

    public function parse(string $rawText): \aportela\MusicBrainzWrapper\ParseHelpers\CoverArtArchiveHelper
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\CoverArtArchive($rawText);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
        $this->raw = $rawText;
        return ($this->parser->parse());
    }
}
