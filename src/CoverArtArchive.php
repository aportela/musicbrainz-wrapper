<?php

namespace aportela\MusicBrainzWrapper;

class CoverArtArchive extends \aportela\MusicBrainzWrapper\Entity
{
    private const DIRECT_IMAGE_URL = "https://coverartarchive.org/release/%s/%s-%s";
    private const GET_API_URL = "https://coverartarchive.org/release/%s/";

    /**
     * @var array<mixed>
     */
    public array $images = [];

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = 0, ?string $cachePath = null)
    {
        if ($apiFormat != \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
        parent::__construct($logger, $apiFormat, $throttleDelayMS, $cachePath);
        $this->reset();
    }

    protected function reset(): void
    {
        parent::reset();
        $this->images = [];
    }

    public function getReleaseImageURL(string $releaseMbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType $imageType, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize $imageSize): string
    {
        return (sprintf(self::DIRECT_IMAGE_URL, $releaseMbId, $imageType->value, $imageSize->value));
    }

    public function get(string $mbId): void
    {
        $this->checkThrottle();
        $url = sprintf(self::GET_API_URL, $mbId);
        $response = $this->httpGET($url);
        if ($response->code == 200) {
            $this->resetThrottle();
            $this->parse($response->body);
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

    public function parse(string $rawText): void
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $json = $this->parseJSON($rawText);
            $releaseURL = isset($json->{"release"}) ? (string) $json->{"release"} : null;
            $releaseURLFields = explode("/", $releaseURL);
            if (! empty($releaseURLFields[0])) {
                $this->mbId = array_pop($releaseURLFields);
            }
            if (isset($json->{"images"})) {
                foreach ($json->{"images"} as $image) {
                    $this->images[] = $image;
                }
            }
            $this->raw = $rawText;
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
    }
}
