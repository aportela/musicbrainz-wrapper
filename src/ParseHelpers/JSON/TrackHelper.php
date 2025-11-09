<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class TrackHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)($object->id ?? null);
        $this->position = intval($object->position ?? 0);
        $this->number = intval($object->number ?? 0);
        if (isset($object->recording)) {
            $this->recording = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\RecordingHelper($object->recording);
        }
    }
}
