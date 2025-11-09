<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->type = \aportela\MusicBrainzWrapper\ArtistType::fromString((string)($element->attributes()->type ?? null));
        $this->name = (string) $element->children()->name;
        $this->country = isset($element->children()->country) ? (!empty($country = $element->children()->country) ? mb_strtolower($country) : null) : null;

        $genreList = $element->children()->{"genre-list"};
        if ($genreList !== null && $children = $genreList->children()) {
            foreach ($children as $child) {
                $this->genres[] = mb_strtolower(mb_trim($child->children()->name));
            }
            if (count($this->genres) > 0) {
                $this->genres = array_unique($this->genres);
            }
        }

        $relationList = $element->children()->{"relation-list"};
        if ($relationList !== null && $children = $relationList->children()) {
            foreach ($children as $child) {
                $this->relations[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ArtistRelationHelper($child);
            }
        }
    }
}
