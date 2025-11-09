<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class RecordingHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->title = (string) $element->children()->title;

        $children = $element->children()->{"artist-credit"}->children()->{"name-credit"};
        if ($children !== null) {
            foreach ($children as $child) {
                $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper($child->children()->artist);
            }
        }
    }
}
