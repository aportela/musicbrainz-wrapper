<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class RecordingHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper
{
    public function __construct(object $object)
    {
        $this->mbId = property_exists($object, "id") && is_string($object->id) ? $object->id : "";
        $this->title = property_exists($object, "title") && is_string($object->title) ? $object->title : "";
        if (property_exists($object, "artist-credit") && is_array($object->{"artist-credit"})) {
            foreach ($object->{"artist-credit"} as $artistObject) {
                if (is_object($artistObject) && property_exists($artistObject, "artist") && is_object($artistObject->artist)) {
                    $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistObject->artist);
                }
            }
        }
    }
}
