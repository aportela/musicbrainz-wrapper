<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class RecordingHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)$object->id;
        $this->title = (string)$object->title;
        if (isset($object->{"artist-credit"}) && is_array($object->{"artist-credit"})) {
            foreach ($object->{"artist-credit"} as $artistObject) {
                $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistObject->artist);
            }
        }
    }
}
