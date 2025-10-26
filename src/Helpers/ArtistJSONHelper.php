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
}
