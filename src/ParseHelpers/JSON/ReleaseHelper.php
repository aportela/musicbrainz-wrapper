<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)($object->id ?? null);
        $this->title = (string)($object->title ?? null);
        $this->year = $this->parseDateToYear($object->date ?? null);

        if (isset($object->{"artist-credit"}) && is_array($object->{"artist-credit"})) {
            foreach ($object->{"artist-credit"} as $artistObject) {
                $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistObject->artist);
            }
        }

        if (isset($object->{"cover-art-archive"})) {
            $this->coverArtArchive = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\CoverArtArchiveHelper($object->{"cover-art-archive"});
        }

        if (isset($object->media) && is_array($object->media)) {
            foreach ($object->media as $mediaObject) {
                $this->media[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\MediaHelper($mediaObject);
            }
        }
    }
}
