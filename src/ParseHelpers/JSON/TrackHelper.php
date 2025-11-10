<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class TrackHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper
{
    public function __construct(object $object)
    {
        $this->mbId = property_exists($object, "id") && is_string($object->id) ? $object->id : "";
        $this->position = property_exists($object, "position") && is_numeric($object->position) ? intval($object->position) : 0;
        $this->number =  property_exists($object, "number") && is_numeric($object->number) ? intval($object->number) : 0;
        if (property_exists($object, "recording") && is_object($object->recording)) {
            $this->recording = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\RecordingHelper($object->recording);
        }
    }
}
