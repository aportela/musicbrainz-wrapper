<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Search;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper>
     */
    public function parse(): array
    {
        $artistsXPath = $this->getXPath("//" . $this->getNS() . ":artist-list/" . $this->getNS() . ":artist");
        if ($artistsXPath === false) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist-list xpath not found");
        }
        
        $results = [];
        if (is_array($artistsXPath) && $artistsXPath !== []) {
            foreach ($artistsXPath as $artistXPath) {
                $results[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper($artistXPath);
            }
        }
        
        return ($results);
    }
}
