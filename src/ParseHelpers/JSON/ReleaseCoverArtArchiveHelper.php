<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ReleaseCoverArtArchiveHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseCoverArtArchiveHelper
{
    public function __construct(object $object)
    {
        if (isset($object->artwork) && is_string($object->artwork)) {
            $this->artwork = $object->artwork === "true";
        }
        if (isset($object->front) && is_string($object->front)) {
            $this->front = $object->front === "true";
        }
        if (isset($object->back) && is_string($object->back)) {
            $this->back =  $object->back === "true";
        }
    }
}
