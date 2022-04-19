<?php

namespace aportela\MusicBrainzWrapper;

use stdClass;

class Entity
{
    const USER_AGENT = "MusicBrainzWrapper - https://github.com/aportela/musicbrainz-wrapper (766f6964+github@gmail.com)";

    protected $logger;
    protected $http;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->debug("MusicBrainzWrapper::__construct");
        $loadedExtensions = get_loaded_extensions();
        if (!in_array("libxml", $loadedExtensions)) {
            $this->logger->critical("MusicBrainzWrapper::__construct ERROR: libxml extension not found");
            throw new \aportela\MusicBrainzWrapper\Exception\LibXMLMissingException("loaded extensions: " . implode(", ", $loadedExtensions));
        } else if (!in_array("SimpleXML", $loadedExtensions)) {
            $this->logger->critical("MusicBrainzWrapper::__construct ERROR: SimpleXML extension not found");
            throw new \aportela\MusicBrainzWrapper\Exception\SimpleXMLMissingException("loaded extensions: " . implode(", ", $loadedExtensions));
        } else {
            $this->logger->debug("MusicBrainzWrapper::__construct");
            $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger, self::USER_AGENT);
        }
    }

    public function __destruct()
    {
        $this->logger->debug("MusicBrainzWrapper::__destruct");
    }
}
