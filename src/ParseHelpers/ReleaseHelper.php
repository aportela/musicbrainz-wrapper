<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\BaseHelper
{
    public string $mbId;
    public string $title;
    public ?int $year = null;

    /**
     * @var array<\aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper>
     */
    public array $artistCredit = [];

    public object $coverArtArchive;

    /**
     * @var array<\aportela\MusicBrainzWrapper\ParseHelpers\MediaHelper>
     */
    public array $media = [];

    public function __construct()
    {
        $this->coverArtArchive = (object)
        [
            "artwork" => false,
            "front" => false,
            "back" => false
        ];
    }
}
