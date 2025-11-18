<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->type = \aportela\MusicBrainzWrapper\ArtistType::tryFrom((string)($element->attributes()->type ?? null)) ?? \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $this->name = (string) $element->children()->name;
        $this->country = property_exists($element->children(), 'country') && $element->children()->country !== null ? (empty($country = $element->children()->country) ? null : mb_strtolower(strval($country))) : null;

        $genreList = $element->children()->{"genre-list"};
        if ($genreList !== null && $children = $genreList->children()) {
            foreach ($children as $child) {
                $this->genres[] = mb_strtolower(mb_trim(strval($child->children()->name)));
            }

            if ($this->genres !== []) {
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
