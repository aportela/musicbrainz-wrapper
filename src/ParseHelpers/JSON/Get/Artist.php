<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper
    {
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($this->json));
    }
}
