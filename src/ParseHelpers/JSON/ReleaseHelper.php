<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
{
    public function __construct(object $object)
    {
        parent::__construct();
        $this->mbId = (string)($object->id ?? null);
        $this->title = (string)($object->title ?? null);
        $this->year = $this->parseDateToYear($object->date ?? null);

        if (isset($object->{"artist-credit"}) && is_array($object->{"artist-credit"})) {
            foreach ($object->{"artist-credit"} as $artistObject) {
                $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistObject->artist);
            }
        }

        if (isset($object->{"cover-art-archive"})) {
            if (isset($object->{"cover-art-archive"}->artwork) && is_string($object->{"cover-art-archive"}->artwork)) {
                $this->coverArtArchive->artwork = $object->{"cover-art-archive"}->artwork === "true";
            }
            if (isset($object->{"cover-art-archive"}->front) && is_string($object->{"cover-art-archive"}->front)) {
                $this->coverArtArchive->front = $object->{"cover-art-archive"}->front === "true";
            }
            if (isset($object->{"cover-art-archive"}->back) && is_string($object->{"cover-art-archive"}->back)) {
                $this->coverArtArchive->back =  $object->{"cover-art-archive"}->back === "true";
            }
        }

        if (isset($object->media) && is_array($object->media)) {
            foreach ($object->media as $mediaObject) {
                $this->media[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\MediaHelper($mediaObject);
            }
        }
    }
}
