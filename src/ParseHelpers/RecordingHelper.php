<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class RecordingHelper
{
    public string $mbId;
    public string $title;
    /**
     * @var array<mixed>
     */
    public array $artistCredit = [];
}
