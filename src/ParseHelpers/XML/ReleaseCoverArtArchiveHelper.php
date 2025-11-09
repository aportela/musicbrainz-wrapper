<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class ReleaseCoverArtArchiveHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseCoverArtArchiveHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        if (property_exists($element, 'artwork') && $element->artwork !== null) {
            $this->artwork = (string)$element->artwork === "true";
        }
        
        if (property_exists($element, 'front') && $element->front !== null) {
            $this->front = (string)$element->front === "true";
        }
        
        if (property_exists($element, 'back') && $element->back !== null) {
            $this->back =  (string) $element->back === "true";
        }
    }
}
