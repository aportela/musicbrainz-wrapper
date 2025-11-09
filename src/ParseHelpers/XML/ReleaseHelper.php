<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->title = (string) $element->children()->title;
        $this->year = $this->parseDateToYear((string) $element->children()->date);

        $children = $element->children()->{"artist-credit"}->children()->{"name-credit"};
        if ($children !== null) {
            foreach ($children as $child) {
                $this->artistCredit[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper($child->children()->artist);
            }
        }

        $covertArtArchive = $element->children()->{"cover-art-archive"};
        if ($covertArtArchive !== null) {
            $this->coverArtArchive = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ReleaseCoverArtArchiveHelper($covertArtArchive);
        }

        $mediaList = $element->children()->{"medium-list"};
        if ($mediaList !== null && $mediaList->attributes() instanceof \SimpleXMLElement && intval($mediaList->attributes()->count) > 0) {
            foreach ($mediaList->children() as $media) {
                $this->media[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\MediaHelper($media);
            }
        }
    }
}
