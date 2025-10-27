<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class Release extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): mixed
    {
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ReleaseHelper($this->json));
    }
}
