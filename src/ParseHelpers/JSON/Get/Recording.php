<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class Recording extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): \aportela\MusicBrainzWrapper\ParseHelpers\JSON\RecordingHelper
    {
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\RecordingHelper($this->json));
    }
}
