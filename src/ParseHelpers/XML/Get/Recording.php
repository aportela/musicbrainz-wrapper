<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Get;

class Recording extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    public function parse(): mixed
    {
        $recordingXPath = $this->getXPath("//" . $this->getNS() . ":recording");
        if ($recordingXPath !== false && is_array($recordingXPath) && count($recordingXPath) == 1) {
            return (new \aportela\MusicBrainzWrapper\ParseHelpers\XML\RecordingHelper($recordingXPath[0]));
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("recording xpath not found");
        }
    }
}
