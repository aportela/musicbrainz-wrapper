<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class CoverArtArchiveHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\CoverArtArchiveHelper
{
    public function __construct(object $object)
    {
        if (isset($object->release)) {
            $releaseUrl = property_exists($object, "release") && is_string($object->release) ? $object->release : "";
            if ($releaseUrl !== '' && $releaseUrl !== '0') {
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
