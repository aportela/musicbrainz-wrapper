<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\BaseHelper
{
    public \aportela\MusicBrainzWrapper\ArtistType $type = \aportela\MusicBrainzWrapper\ArtistType::NONE;
    public string $name;
    public ?string $country = null;

    /**
     * @var array<string>
     */
    public array $genres = [];

    /**
     * @var array<\aportela\MusicBrainzWrapper\ParseHelpers\ArtistRelationHelper>
     */
    public array $relations = [];

    /**
     * @return array<string>
     */
    public function getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType $typeId): array
    {
        return array_map(
            fn ($relation) => $relation->url,
            array_values(
                array_filter(
                    $this->relations,
                    fn ($relation) => $relation->typeId == $typeId->value
                )
            )
        );
    }
}
