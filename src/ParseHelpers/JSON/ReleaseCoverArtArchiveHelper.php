<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ReleaseCoverArtArchiveHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseCoverArtArchiveHelper
{
    public function __construct(object $object)
    {
        if (property_exists($object, "artwork") && is_bool($object->artwork)) {
            $this->artwork = $object->artwork;
        }

        if (property_exists($object, "front") && is_bool($object->front)) {
            $this->front = $object->front;
        }

        if (property_exists($object, "back") && is_bool($object->back)) {
            $this->back =  $object->back;
        }
    }
}
