<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Get;

class Release extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    public function parse(): \aportela\MusicBrainzWrapper\ParseHelpers\XML\ReleaseHelper
    {
        $releaseXPath = $this->getXPath("//" . $this->getNS() . ":release");
        if ($releaseXPath !== false && (is_array($releaseXPath) && count($releaseXPath) === 1)) {
            return (new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ReleaseHelper($releaseXPath[0]));
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("release xpath not found");
        }
    }
}
