<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class TrackHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)$object->id;
        $this->position = intval($object->position);
        $this->number = intval($object->number);
        $this->recording = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\RecordingHelper($object->recording);
    }
}
