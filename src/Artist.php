<?php

namespace aportela\MusicBrainzWrapper;

class Artist extends \aportela\MusicBrainzWrapper\Entity
{
    private const SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=%s&limit=%d&fmt=%s";
    private const GET_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=genres+recordings+releases+release-groups+works+url-rels&fmt=%s";

    /**
     * This is a Special Purpose Artist that should only be used if no artist of discographic relevance has been attributed to a piece of work.
     * https://musicbrainz.org/artist/eec63d3c-3b81-4ad4-b1e4-7c147d4d2b61
     */
    public const NO_ARTIST_MB_ID = "eec63d3c-3b81-4ad4-b1e4-7c147d4d2b61";

    public \aportela\MusicBrainzWrapper\ArtistType $type = \aportela\MusicBrainzWrapper\ArtistType::NONE;

    public ?string $name = null;

    public ?string $country = null;
    /**
     * @var array<string>
     */
    public array $genres = [];
    /**
     * @var array<mixed>
     */
    public array $relations = [];

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = 0, ?string $cachePath = null)
    {
        parent::__construct($logger, $apiFormat, $throttleDelayMS, $cachePath);
        $this->reset();
    }

    protected function reset(): void
    {
        parent::reset();
        $this->type = \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $this->name = null;
        $this->country = null;
        $this->genres = [];
        $this->relations = [];
    }

    /**
     * @return array<mixed>
     */
    public function search(string $name, int $limit = 1): array
    {
        $this->checkThrottle();
        $url = sprintf(self::SEARCH_API_URL, urlencode($name), $limit, $this->apiFormat->value);
        $response = $this->httpGET($url);
        if ($response->code == 200) {
            $this->resetThrottle();
            if ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::XML) {
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Search\Artist($response->body);
            } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search\Artist($response->body);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
            }
            $results = $this->parser->parse();
            if (count($results) > 0) {
                return ($results);
            } else {
                throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException("artist name: {$name}", 0);
            }
        } elseif ($response->code == 503) {
            $this->incrementThrottle();
            throw new \aportela\MusicBrainzWrapper\Exception\RateLimitExceedException($name, $response->code);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\HTTPException($name, $response->code);
        }
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
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Artist($rawText);
        } elseif ($this->apiFormat == \aportela\MusicBrainzWrapper\APIFormat::JSON) {
            $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Artist($rawText);
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("");
        }
        $data = $this->parser->parse();
        $this->mbId = $data->mbId;
        $this->type = $data->type;
        $this->name = $data->name;
        $this->country = $data->country;
        $this->genres = $data->genres;
        $this->relations = $data->relations;
        $this->raw = $rawText;
    }

    /**
     * @return array<string>
     */
    public function getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType $typeId): array
    {
        return array_map(
            fn($relation) => $relation->url,
            array_values(
                array_filter(
                    $this->relations,
                    fn($relation) => $relation->typeId == $typeId->value
                )
            )
        );
    }
}
