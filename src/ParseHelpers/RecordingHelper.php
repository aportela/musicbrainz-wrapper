<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class RecordingHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\BaseHelper
{
    public string $title;

    /**
     * @var array<\aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper>
     */
    public array $artistCredit = [];
}
