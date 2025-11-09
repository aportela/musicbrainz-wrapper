<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class MediaHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\BaseHelper
{
    public int $position;

    /**
     * @var array<\aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper>
     */

    public array $trackList = [];
}
