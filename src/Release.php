<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper;

class Release extends \aportela\MusicBrainzWrapper\Entity
{
    private const string SEARCH_API_URL = "http://musicbrainz.org/ws/2/release/?query=%s&limit=%d&fmt=%s";

    private const string GET_API_URL = "https://musicbrainz.org/ws/2/release/%s?inc=artist-credits+recordings+url-rels&fmt=%s";

    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper>
     */
    public function search(string $title, string $artist, string $year, int $limit = 1): array
    {
        $queryParams = [
            "release:" . urlencode($title),
        ];
        if ($artist !== '' && $artist !== '0') {
            $queryParams[] = "artistname:" . urlencode($artist);
        }

        if ($year !== '' && $year !== '0' && mb_strlen($year) === 4) {
            $queryParams[] = "date:" . urlencode($year);
        }

        $url = sprintf(self::SEARCH_API_URL, implode(urlencode(" AND "), $queryParams), $limit, $this->apiFormat->value);
        $responseBody = $this->httpGET($url);
        if (!in_array($responseBody, [null, '', '0'], true)) {
            switch ($this->apiFormat) {
                case \aportela\MusicBrainzWrapper\APIFormat::XML:
                    $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Search\Release($responseBody);
                    break;
                case \aportela\MusicBrainzWrapper\APIFormat::JSON:
                    $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search\Release($responseBody);
                    break;
                default:
                    $this->logger->error(\aportela\MusicBrainzWrapper\Release::class . '::search - Error: invalid API format', [$this->apiFormat]);
                    /** @var string $format */
                    $format = $this->apiFormat->value;
                    throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat('Invalid API format: ' . $format);
            }

            return ($this->parser->parse());
        } else {
            $this->logger->error(\aportela\MusicBrainzWrapper\Release::class . '::search - Error: empty body on API response', [$url]);
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIResponse('Empty body on API response for URL: ' . $url);
        }
    }

    public function get(string $mbId): \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
    {
        if (! $this->getCache($mbId)) {
            $url = sprintf(self::GET_API_URL, $mbId, $this->apiFormat->value);
            $responseBody = $this->httpGET($url);
            if (!in_array($responseBody, [null, '', '0'], true)) {
                $this->saveCache($mbId, $responseBody);
                return ($this->parse($responseBody));
            } else {
                $this->logger->error(\aportela\MusicBrainzWrapper\Release::class . '::get - Error: empty body on API response', [$url]);
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIResponse('Empty body on API response for URL: ' . $url);
            }
        } elseif (!in_array($this->raw, [null, '', '0'], true)) {
            return ($this->parse($this->raw));
        } else {
            $this->logger->error(\aportela\MusicBrainzWrapper\Release::class . '::get - Error: cached data for identifier is empty', [$mbId]);
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidCacheException(sprintf('Cached data for identifier (%s) is empty', $mbId));
        }
    }

    public function parse(string $rawText): \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
    {
        $this->reset();
        switch ($this->apiFormat) {
            case \aportela\MusicBrainzWrapper\APIFormat::XML:
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\Get\Release($rawText);
                break;
            case \aportela\MusicBrainzWrapper\APIFormat::JSON:
                $this->parser = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get\Release($rawText);
                break;
            default:
                $this->logger->error(\aportela\MusicBrainzWrapper\Release::class . '::parse - Error: invalid API format', [$this->apiFormat]);
                /** @var string $format */
                $format = $this->apiFormat->value;
                throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat('Invalid API format: ' . $format);
        }

        $this->raw = $rawText;
        return ($this->parser->parse());
    }
}
