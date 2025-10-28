<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Get;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    public function parse(): mixed
    {
        $artistXPath = $this->getXPath("//" . $this->getNS() . ":artist");
        if ($artistXPath === false || count($artistXPath) != 1) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist xpath not found");
        }
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper($artistXPath[0]));
    }
}
