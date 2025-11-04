<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class MediaHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\MediaHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)($object->id ?? null);
        // on search api response, this json property is missing
        $this->position = intval($object->position ?? 0);

        if (isset($object->{"track-count"}) && intval($object->{"track-count"}) > 0 && isset($object->tracks) && is_array($object->tracks)) {
            foreach ($object->tracks as $trackObject) {
                $this->trackList[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\TrackHelper($trackObject);
            }
        }
    }
}
