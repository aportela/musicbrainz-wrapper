<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Get;

class Release extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    public function parse(): mixed
    {
        $releaseXPath = $this->getXPath("//" . $this->getNS() . ":release");
        if ($releaseXPath === false || count($releaseXPath) != 1) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("release xpath not found");
        }
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ReleaseHelper($releaseXPath[0]));
    }
}
