<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): mixed
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
