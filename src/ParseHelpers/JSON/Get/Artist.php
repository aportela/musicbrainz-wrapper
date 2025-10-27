<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Get;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): mixed
    {
        $data = (object) ["mbId" => null, "type" => null, "name" => null, "country" => null, "genres" => [], "relations" => []];
        $data->mbId = (string)$this->json->{"id"};
        $data->type = \aportela\MusicBrainzWrapper\ArtistType::fromString($this->json->{"type"}) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $data->name = (string)$this->json->{"name"};
        $data->country = !empty($country = $this->json->{"country"}) ? mb_strtolower($country) : null;
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
