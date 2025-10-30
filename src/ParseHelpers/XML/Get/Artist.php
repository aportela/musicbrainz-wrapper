<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Get;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    public function parse(): mixed
    {
        $artistXPath = $this->getXPath("//" . $this->getNS() . ":artist");
        if ($artistXPath !== false && is_array($artistXPath) && count($artistXPath) == 1) {
            return (new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistHelper($artistXPath[0]));
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist xpath not found");
        }
    }
}
