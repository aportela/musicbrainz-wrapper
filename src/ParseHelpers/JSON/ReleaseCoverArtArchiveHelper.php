<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ReleaseCoverArtArchiveHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseCoverArtArchiveHelper
{
    public function __construct(object $object)
    {
        if (isset($object->artwork) && is_bool($object->artwork)) {
            $this->artwork = $object->artwork;
        }
        if (isset($object->front) && is_bool($object->front)) {
            $this->front = $object->front;
        }
        if (isset($object->back) && is_bool($object->back)) {
            $this->back =  $object->back;
        }
    }
}
