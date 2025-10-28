<?php

namespace aportela\MusicBrainzWrapper;

class Recording extends \aportela\MusicBrainzWrapper\Entity
{
    public const GET_API_URL = "https://musicbrainz.org/ws/2/recording/%s?inc=artist-credits&fmt=%s";

    public ?string $title = null;
    /**
     * @var array<mixed>
     */
    public array $artistCredit = [];

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS, ?string $cachePath = null)
    {
        parent::__construct($logger, $apiFormat, $throttleDelayMS, $cachePath);
        $this->reset();
    }

    protected function reset(): void
    {
        parent::reset();
        $this->title = null;
        $this->artistCredit = [];
    }

    public function get(string $mbId): void
    {
        if (! $this->getCache($mbId)) {
            $this->checkThrottle();
            $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
            $response = $this->httpGET($url);
            if ($response->code == 200) {
                $this->resetThrottle();
                $this->saveCache($mbId, $response->body);
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
        } else {
            $this->parse($this->raw);
        }
    }

    public function parse(string $rawText): void
    {
        $this->reset();
        if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Recording($rawText);
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Recording($rawText);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
        $data = $this->parser->parse();
        $this->mbId = $data->mbId;
        $this->title = $data->title;
        $this->artistCredit = $data->artistCredit;
        $this->raw = $rawText;
    }
}
