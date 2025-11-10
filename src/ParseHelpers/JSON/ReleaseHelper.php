<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
{
    public function __construct(object $object)
    {
        $this->mbId = property_exists($object, "id") && is_string($object->id) ? $object->id : "";
        $this->title = property_exists($object, "title") && is_string($object->title) ? $object->title : "";
        $this->year = property_exists($object, "date") && is_string($object->date) ? $this->parseDateToYear($object->date) : null;

        if (property_exists($object, "artist-credit") && is_array($object->{"artist-credit"})) {
            foreach ($object->{"artist-credit"} as $artistObject) {
                if (is_object($artistObject) && property_exists($artistObject, "artist") && is_object($artistObject->artist)) {
                    $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistObject->artist);
                }
            }
        }

        if (property_exists($object, "cover-art-archive") && is_object($object->{"cover-art-archive"})) {
            $this->coverArtArchive = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ReleaseCoverArtArchiveHelper($object->{"cover-art-archive"});
        }

        if (property_exists($object, "media") && is_array($object->media)) {
            foreach ($object->media as $mediaObject) {
                if (is_object($mediaObject)) {
                    $this->media[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\MediaHelper($mediaObject);
                }
            }
        }
    }
}
