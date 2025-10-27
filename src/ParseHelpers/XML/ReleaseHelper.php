<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        parent::__construct();
        $this->mbId = (string) $element->attributes()->id;
        $this->title = (string) $element->children()->title;
        $this->year = $this->parseDate((string) $element->children()->date);

        $children = $element->children()->{"artist-credit"}->children()->{"name-credit"};
        if ($children !== false) {
            foreach ($children as $artistElement) {
                $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper($artistElement->children()->artist);
            }
        }

        $covertArtArchive = $element->children()->{"cover-art-archive"};
        if ($covertArtArchive !== null) {
            $coverArt = $covertArtArchive->children();
            if ($coverArt !== null) {
                $this->coverArtArchive->artwork = ((string) $coverArt->artwork) === "true";
                $this->coverArtArchive->front = ((string) $coverArt->front) === "true";
                $this->coverArtArchive->back = ((string) $coverArt->back) === "true";
            }
        }

        $mediaList = $element->children()->{"medium-list"};
        if ($mediaList !== false && $mediaList->attributes() !== null && intval($mediaList->attributes()->count) > 0) {
            foreach ($mediaList->children() as $media) {
                $this->media[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\MediaHelper($media);
            }
        }
    }
}
