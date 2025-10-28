<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class TrackHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->position = intval($element->children()->position);
        $this->number = intval($element->children()->number);
        $this->recording = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\RecordingHelper($element->children()->recording);
    }
}
