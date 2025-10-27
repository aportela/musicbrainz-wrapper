<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class MediaHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\MediaHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->position = intval($element->children()->position);

        $trackListElement = $element->children()->{"track-list"};
        if ($trackListElement !== false && intval($trackListElement->attributes()->count) > 0) {
            foreach ($trackListElement->children() as $track) {
                $this->trackList[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\TrackHelper($track);
            }
        }
    }
}
