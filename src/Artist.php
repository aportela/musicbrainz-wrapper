<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper;

class Artist extends \aportela\MusicBrainzWrapper\Entity
{
    private const string SEARCH_API_URL = "http://musicbrainz.org/ws/2/artist/?query=%s&limit=%d&fmt=%s";

    private const string GET_API_URL = "https://musicbrainz.org/ws/2/artist/%s?inc=genres+recordings+releases+release-groups+works+url-rels&fmt=%s";

    /**
     * This is a Special Purpose Artist that should only be used if no artist of discographic relevance has been attributed to a piece of work.
     * https://musicbrainz.org/artist/eec63d3c-3b81-4ad4-b1e4-7c147d4d2b61
     */
    public const string NO_ARTIST_MB_ID = "eec63d3c-3b81-4ad4-b1e4-7c147d4d2b61";

    public const string VARIOUS_ARTISTS_MB_ID = "89ad4ac3-39f7-470e-963a-56509c546377";

    public \aportela\MusicBrainzWrapper\ArtistType $type = \aportela\MusicBrainzWrapper\ArtistType::NONE;

    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper>
     */
    public function search(string $name, int $limit = 1): array
    {
        $url = sprintf(self::SEARCH_API_URL, urlencode($name), $limit, $this->apiFormat->value);
        $responseBody = $this->httpGET($url);
        $responseBody = $this->httpGET($url);
        if (!in_array($responseBody, [null, '', '0'], true)) {
            switch ($this->apiFormat) {
                case \aportela\MusicBrainzWrapper\APIFormat::XML:
                    $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Search\Artist($responseBody);
                    break;
                case \aportela\MusicBrainzWrapper\APIFormat::JSON:
                    $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search\Artist($responseBody);
                    break;
                default:
                    $this->logger->error(\aportela\MusicBrainzWrapper\Artist::class . '::search - Error: invalid API format', [$this->apiFormat]);
                    /** @var string $format */
                    $format = $this->apiFormat->value;
                    throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat('Invalid API format: ' . $format);
            }

            return ($this->parser->parse());
        } else {
            $this->logger->error(\aportela\MusicBrainzWrapper\Artist::class . '::search - Error: empty body on API response', [$url]);
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIResponse('Empty body on API response for URL: ' . $url);
        }
    }

    public function get(string $mbId): \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
    {
        if (! $this->getCache($mbId)) {
            $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
            $responseBody = $this->httpGET($url);
            if (!in_array($responseBody, [null, '', '0'], true)) {
                $this->saveCache($mbId, $responseBody);
                return ($this->parse($responseBody));
            } else {
                $this->logger->error(\aportela\MusicBrainzWrapper\Artist::class . '::get - Error: empty body on API response', [$url]);
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIResponse('Empty body on API response for URL: ' . $url);
            }
        } elseif (!in_array($this->raw, [null, '', '0'], true)) {
            return ($this->parse($this->raw));
        } else {
            $this->logger->error(\aportela\MusicBrainzWrapper\Artist::class . '::get - Error: cached data for identifier is empty', [$mbId]);
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidCacheException(sprintf('Cached data for identifier (%s) is empty', $mbId));
        }
    }

    public function parse(string $rawText): \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
    {
        $this->reset();
        switch ($this->apiFormat) {
            case \aportela\MusicBrainzWrapper\APIFormat::XML:
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Artist($rawText);
                break;
            case \aportela\MusicBrainzWrapper\APIFormat::JSON:
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Artist($rawText);
                break;
            default:
                $this->logger->error(\aportela\MusicBrainzWrapper\Artist::class . '::parse - Error: invalid API format', [$this->apiFormat]);
                /** @var string $format */
                $format = $this->apiFormat->value;
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat('Invalid API format: ' . $format);
        }

        $this->raw = $rawText;
        return ($this->parser->parse());
    }
}
