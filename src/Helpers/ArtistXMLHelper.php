<?php

namespace aportela\MusicBrainzWrapper\Helpers;

class ArtistXMLHelper extends XMLHelper
{

    public function __construct(string $raw)
    {
        parent::__construct($raw);
    }

    public function parseSearchResponse(): array
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

    public function parseGetResponse()
    {
        $data = (object) ["mbId" => null, "type" => null, "name" => null, "country" => null, "genres" => [], "relations" => []];
        $artistXPath = $this->getXPath("//" . $this->getNS() . ":artist");
        if ($artistXPath === false || count($artistXPath) != 1) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist xpath not found");
        }
        $data->mbId = (string)$artistXPath[0]->attributes()->id ?: null;
        $data->type = \aportela\MusicBrainzWrapper\ArtistType::fromString($artistXPath[0]->attributes()->type) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $data->name = (string)$artistXPath[0]->children()->name ?: null;
        $data->country = !empty($country = $artistXPath[0]->children()->country) ? mb_strtolower($country) : null;

        $genreList = $artistXPath[0]->children()->{"genre-list"};
        if ($genreList !== false && count($genreList) > 0) {
            $genres = $genreList->children();
            if (! empty($genres)) {
                foreach ($genres as $genre) {
                    $data->genres[] = mb_strtolower(trim($genre->children()->name));
                }
                $data->genres = array_unique($data->genres);
            }
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist genre-list children not found");
        }
        $relationList = $artistXPath[0]->children()->{"relation-list"};
        if ($relationList !== false && count($relationList) > 0) {
            $relations = $relationList->children();
            if (! empty($relations)) {
                foreach ($relations as $relation) {
                    $data->relations[] = (object) [
                        "typeId" => (string) $relation->attributes()->{"type-id"},
                        "name" => (string) $relation->attributes()->{"type"},
                        "url" => (string) $relation->{"target"}
                    ];
                }
            }
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("artist relation-list children not found");
        }
        return ($data);
    }
}
