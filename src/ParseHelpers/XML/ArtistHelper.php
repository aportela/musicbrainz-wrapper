<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;


class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->type = isset($element->attributes()->type) ? (\aportela\MusicBrainzWrapper\ArtistType::fromString($element->attributes()->type) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE) : \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $this->name = (string) $element->children()->name;
        $this->country = isset($element->children()->country) ?  (!empty($country = $element->children()->country) ? mb_strtolower($country) : null) : null;

        $genreList = $element->children()->{"genre-list"};
        if ($genreList !== false && $genreList->hasChildren()) {
            foreach ($genreList->children() as $genre) {
                $this->genres[] = mb_strtolower(trim($genre->children()->name));
            }
            if (count($this->genres) > 0) {
                $this->genres = array_unique($this->genres);
            }
        }
        $relationList = $element->children()->{"relation-list"};
        if ($relationList !== false && $relationList->hasChildren()) {
            foreach ($relationList->children() as $relation) {
                $this->relations[] = (object) [
                    "typeId" => (string) $relation->attributes()->{"type-id"},
                    "name" => (string) $relation->attributes()->{"type"},
                    "url" => (string) $relation->{"target"}
                ];
            }
        }
    }
}
