<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
{
    public function __construct(object $object)
    {
        parent::__construct();
        $this->mbId = (string)$object->id;
        $this->title = (string)$object->title;
        $this->year = $this->parseDate($this->date ?? null);

        if (isset($object->{"artist-credit"}) && is_array($object->{"artist-credit"})) {
            foreach ($object->{"artist-credit"} as $artistObject) {
                $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistObject->artist);
            }
        }

        if (isset($object->{"cover-art-archive"})) {
            $this->coverArtArchive->artwork = ((string) $object->{"cover-art-archive"}->artwork) === "true";
            $this->coverArtArchive->front = ((string) $object->{"cover-art-archive"}->front) === "true";
            $this->coverArtArchive->back = ((string) $object->{"cover-art-archive"}->back) === "true";
        }

        if (isset($object->media) && is_array($object->media)) {

            foreach ($object->media as $mediaObject) {
                $this->media[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\MediaHelper($mediaObject);
            }
        }
    }
}
