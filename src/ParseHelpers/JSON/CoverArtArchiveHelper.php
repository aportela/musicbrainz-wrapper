<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class CoverArtArchiveHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\CoverArtArchiveHelper
{
    public function __construct(object $object)
    {
        if (isset($object->release)) {
            $releaseUrl = (string) $object->release ?? null;
            if (! empty($releaseUrl)) {
                $urlParts = explode("/", $releaseUrl);
                $this->mbId = array_pop($urlParts);
            }
        }
        if (isset($object->images) && is_array($object->images)) {
            foreach ($object->images as $image) {
                $this->images[] = $image;
            }
        }
    }
}
