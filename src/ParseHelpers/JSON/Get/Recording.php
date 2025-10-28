<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class Recording extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): mixed
    {
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\RecordingHelper($this->json));
    }
}
