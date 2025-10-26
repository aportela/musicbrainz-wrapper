<?php

namespace aportela\MusicBrainzWrapper\Helpers;

class ArtistJSONHelper extends JSONHelper
{

    public function __construct(string $raw)
    {
        parent::__construct($raw);
    }

    public function parseSearchResponse(): array
    {
        $results = [];
        if ($this->json->{"count"} > 0 && is_array($this->json->{"artists"})) {
            foreach ($this->json->{"artists"} as $artist) {
                $results[] = (object) [
                    "mbId" => (string)$artist->{"id"},
                    "type" => \aportela\MusicBrainzWrapper\ArtistType::fromString($artist->{"type"}) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE,
                    "name" => (string)$artist->{"name"},
                    "country" => isset($artist->{"country"}) ? mb_strtolower($artist->{"country"}) : null
                ];
            }
        }
        return ($results);
    }

    public function parseGetResponse()
    {
        $data = (object) ["mbId" => null, "type" => null, "name" => null, "country" => null, "genres" => [], "relations" => []];
        $data->mbId = (string)$this->json->{"id"};
        $data->type = \aportela\MusicBrainzWrapper\ArtistType::fromString($this->json->{"type"}) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $data->name = (string)$this->json->{"name"};
        $data->country = isset($this->json->{"country"}) ? mb_strtolower($this->json->{"country"}) : null;
        if (isset($this->json->{"genres"}) && is_array(($this->json->{"genres"}))) {
            foreach ($this->json->{"genres"} as $genre) {
                $data->genres[] = mb_strtolower(trim($genre->{"name"}));
            }
            $data->genres = array_unique($data->genres);
        }
        if (isset($this->json->{"relations"}) && is_array($this->json->{"relations"})) {
            foreach ($this->json->{"relations"} as $relation) {
                $data->relations[] = (object) [
                    "typeId" => (string) $relation->{"type-id"},
                    "name" => (string)$relation->{"type"},
                    "url" => (string)$relation->{"url"}->{"resource"}
                ];
            }
        }
        return ($data);
    }
}
