<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class Release extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ReleaseHelper
    {
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ReleaseHelper($this->json));
    }
}
