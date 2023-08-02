<?php

namespace aportela\MusicBrainzWrapper;

class Entity
{
    const USER_AGENT = "MusicBrainzWrapper - https://github.com/aportela/musicbrainz-wrapper (766f6964+github@gmail.com)";
    const API_FORMAT_XML = "xml";
    const API_FORMAT_JSON = "json";

    protected $logger;
    protected $http;
    protected $apiFormat;

    public ?string $mbId;
    public ?string $raw;

    public function __construct(\Psr\Log\LoggerInterface $logger, string $apiFormat)
    {
        $this->logger = $logger;
        $this->logger->debug("MusicBrainzWrapper::__construct");
        $supportedApiFormats = [self::API_FORMAT_XML, self::API_FORMAT_JSON];
        if (!in_array($apiFormat, $supportedApiFormats)) {
            $this->logger->critical("MusicBrainzWrapper::__construct ERROR: invalid api format");
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidAPIFormat("supported formats: " . implode(", ", $supportedApiFormats));
        }
        $this->apiFormat = $apiFormat;
        $loadedExtensions = get_loaded_extensions();
        if (!in_array("libxml", $loadedExtensions)) {
            $this->logger->critical("MusicBrainzWrapper::__construct ERROR: libxml extension not found");
            throw new \aportela\MusicBrainzWrapper\Exception\LibXMLMissingException("loaded extensions: " . implode(", ", $loadedExtensions));
        } else if (!in_array("SimpleXML", $loadedExtensions)) {
            $this->logger->critical("MusicBrainzWrapper::__construct ERROR: SimpleXML extension not found");
            throw new \aportela\MusicBrainzWrapper\Exception\SimpleXMLMissingException("loaded extensions: " . implode(", ", $loadedExtensions));
        } else {
            $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger, self::USER_AGENT);
        }
    }

    public function __destruct()
    {
        $this->logger->debug("MusicBrainzWrapper::__destruct");
    }
}
