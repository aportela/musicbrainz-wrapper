<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)$object->id;
        $this->type = isset($object->type) ? (\aportela\MusicBrainzWrapper\ArtistType::fromString($object->type) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE) : \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $this->name = (string)$object->name;
        $this->country = isset($object->country) ? (!empty($country = $object->country) ? mb_strtolower($country) : null) : null;

        if (isset($object->genres) && is_array(($object->genres))) {
            foreach ($object->{"genres"} as $genre) {
                $this->genres[] = mb_strtolower(trim($genre->name));
            }
            $this->genres = array_unique($this->genres);
        }
        if (isset($object->relations) && is_array($object->relations)) {
            foreach ($object->relations as $relation) {
                $this->relations[] = (object) [
                    "typeId" => (string) $relation->{"type-id"},
                    "name" => (string)$relation->type,
                    "url" => (string)$relation->url->resource
                ];
            }
        }
    }
}
