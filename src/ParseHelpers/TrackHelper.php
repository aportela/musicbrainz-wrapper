<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class TrackHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\BaseHelper
{
    public int $position;
    
    public int $number;
    
    public \aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper $recording;
}
