<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class CoverArtArchive extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): mixed
    {
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\CoverArtArchiveHelper($this->json));
    }
}
