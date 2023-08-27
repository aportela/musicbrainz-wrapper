<?php

namespace aportela\MusicBrainzWrapper;

class CoverArtArchive extends \aportela\MusicBrainzWrapper\Entity
{
    private const DIRECT_IMAGE_URL = "https://coverartarchive.org/release/%s/%s-%s";
    private const GET_API_URL = "https://coverartarchive.org/release/%s/";

    public array $images = [];

    public function getReleaseImageURL(string $releaseMbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType $imageType, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize $imageSize)
    {
        return (sprintf(self::DIRECT_IMAGE_URL, $releaseMbId, $imageType->value, $imageSize->value));
    }

    public function get(string $mbId): void
    {
        $url = sprintf(self::GET_API_URL, $mbId);
        $response = $this->http->GET($url);
        if ($response->code == 200) {
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
    }

    public function parse(string $rawText): void
    {
        $this->mbId = null;
        $this->raw = $rawText;
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $json = json_decode($this->raw);
            $releaseURL = isset($json->{"release"}) ? (string) $json->{"release"} : null;
            $releaseURLFields = explode("/", $releaseURL);
            if (is_array($releaseURLFields) && count($releaseURLFields) > 0) {
                $this->mbId = array_pop($releaseURLFields);
            }
            if (isset($json->{"images"})) {
                foreach ($json->{"images"} as $image) {
                    $this->images[] = $image;
                }
            }
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }
}
