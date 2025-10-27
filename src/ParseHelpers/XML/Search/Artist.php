<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Search;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    public function parse(): mixed
    {
        $artistsXPath = $this->getXPath("//" . $this->getNS() . ":artist-list/" . $this->getNS() . ":artist");
        if ($artistsXPath === false) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist-list xpath not found");
        }
        $results = [];
        if (count($artistsXPath) > 0) {
            foreach ($artistsXPath as $artistElement) {
                $results[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper($artistElement);
            }
        }
        return ($results);
    }
}
