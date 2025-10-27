<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class MediaHelper
{
    public string $mbId;
    public int $position;

    /**
     * @var array<\aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper>
     */

    public array $trackList = [];
}
