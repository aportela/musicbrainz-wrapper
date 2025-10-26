<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Search;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    public function parse(): mixed
    {
        $artistsXPath = $this->getXPath("//" . $this->getNS() . ":artist-list/" . $this->getNS() . ":artist");
        if ($artistsXPath === false) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist-list xpath not found");
        }
        $results = [];
        if (count($artistsXPath) > 0) {
            foreach ($artistsXPath as $artistXPath) {
                $results[] = (object) [
                    "mbId" => (string) $artistXPath->attributes()->id,
                    "type" => \aportela\MusicBrainzWrapper\ArtistType::fromString($artistXPath->attributes()->type) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE,
                    "name" => (string) $artistXPath->children()->name,
                    "country" => !empty($country = $artistXPath->children()->country) ? mb_strtolower($country) : null
                ];
            }
        }
        return ($results);
    }
}
