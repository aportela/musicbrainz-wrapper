<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class CoverArtArchive extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): \aportela\MusicBrainzWrapper\ParseHelpers\JSON\CoverArtArchiveHelper
    {
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\CoverArtArchiveHelper($this->json));
    }
}
