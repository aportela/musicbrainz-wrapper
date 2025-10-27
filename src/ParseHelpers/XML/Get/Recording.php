<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Get;

class Recording extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    public function parse(): mixed
    {
        $recordingXPath = $this->getXPath("//" . $this->getNS() . ":recording");
        if ($recordingXPath === false || count($recordingXPath) != 1) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("recording xpath not found");
        }
        return (new \aportela\MusicBrainzWrapper\ParseHelpers\XML\RecordingHelper($recordingXPath[0]));
    }
}
