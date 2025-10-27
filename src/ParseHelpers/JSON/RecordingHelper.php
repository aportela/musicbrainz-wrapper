<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;


class RecordingHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)$object->id;
        $this->title = (string)$object->title;
        foreach ($object->{"artist-credit"} as $artistObject) {
            $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistObject->artist);
        }
    }
}
