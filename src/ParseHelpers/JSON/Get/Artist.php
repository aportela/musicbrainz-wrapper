<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): mixed
    {
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($this->json));
    }
}
