<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class ArtistHelper
{
    public string $mbId;
    public \aportela\MusicBrainzWrapper\ArtistType $type = \aportela\MusicBrainzWrapper\ArtistType::NONE;
    public string $name;
    public ?string $country = null;
    /**
     * @var array<mixed>
     */
    public array $genres = [];
    /**
     * @var array<mixed>
     */
    public array $relations = [];
}
