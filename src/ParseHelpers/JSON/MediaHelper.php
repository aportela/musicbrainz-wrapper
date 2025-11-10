<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class MediaHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\MediaHelper
{
    public function __construct(object $object)
    {
        $this->mbId = property_exists($object, "id") && is_string($object->id) ? $object->id : "";
        // on search api response, this json property is missing
        $this->position = property_exists($object, "position") && is_numeric($object->position) ? intval($object->position) : 0;

        if (property_exists($object, "track-count") && is_numeric($object->{"track-count"}) && intval($object->{"track-count"}) > 0 && property_exists($object, "tracks") && is_array($object->tracks)) {
            foreach ($object->tracks as $trackObject) {
                if (is_object($trackObject)) {
                    $this->trackList[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\TrackHelper($trackObject);
                }
            }
        }
    }
}
