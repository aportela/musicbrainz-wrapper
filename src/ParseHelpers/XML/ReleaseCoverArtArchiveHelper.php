<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class ReleaseCoverArtArchiveHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseCoverArtArchiveHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        if (isset($element->artwork)) {
            $this->artwork = (string)$element->artwork === "true";
        }
        if (isset($element->front)) {
            $this->front = (string)$element->front === "true";
        }
        if (isset($element->back)) {
            $this->back =  (string) $element->back === "true";
        }
    }
}
