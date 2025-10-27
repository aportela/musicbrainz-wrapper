<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class RecordingHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->title = (string) $element->children()->title;

        $children = $element->children()->{"artist-credit"}->children()->{"name-credit"};
        if ($children !== false) {
            foreach ($children as $artistElement) {
                $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper($artistElement->children()->artist);
            }
        }
    }
}
