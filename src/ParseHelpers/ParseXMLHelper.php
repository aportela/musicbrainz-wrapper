<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

abstract class ParseXMLHelper
{
    protected const DEFAULT_NS_ALIAS = "mmd";
    protected const DEFAULT_NS = "http://musicbrainz.org/ns/mmd-2.0#";

    protected mixed $xml;

    public function __construct(string $raw)
    {
        libxml_clear_errors();
        $this->xml = simplexml_load_string($raw);
        if ($this->xml === false) {

            $errorMessage = "invalid xml";
            $errorCode = 0;
            $lastError = libxml_get_last_error();
            if ($lastError) {
                $errorMessage = "Error: " . $lastError->message . " (Line: " . $lastError->line . ", Column: " . $lastError->column . ")";
                $errorCode = $lastError->code;
            }
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException($errorMessage, $errorCode);
        }
        //$this->xml->registerXPathNamespace(self::DEFAULT_NS_ALIAS, reset($this->xml->getNamespaces(true)) ?? self::DEFAULT_NS);
        $this->xml->registerXPathNamespace(self::DEFAULT_NS_ALIAS, self::DEFAULT_NS);
    }

    protected function getNS()
    {
        return (self::DEFAULT_NS_ALIAS);
    }

    protected function getXPath(string $path): mixed
    {
        return ($this->xml->xpath($path));
    }

    abstract public function parse(): mixed;
}
