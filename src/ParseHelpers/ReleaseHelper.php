<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\BaseHelper
{
    public string $mbId;
    public string $title;
    public ?int $year = null;

    /**
     * @var array<mixed>
     */
    public array $artistCredit = [];

    public object $coverArtArchive;
    /**
     * @var array<mixed>
     */
    public array $media = [];

    // TODO:
    //public array $relations = [];

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
