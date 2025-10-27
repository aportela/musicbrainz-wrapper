<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    public function parse(): mixed
    {
        if (! (isset($this->json->{"count"}) && isset($this->json->{"artists"}))) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidJSONException("artists count/array not found");
        }
        $results = [];
        if ($this->json->{"count"} > 0 && is_array($this->json->{"artists"})) {
            foreach ($this->json->{"artists"} as $artistElement) {
                $results[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistElement);
            }
        }
        return ($results);
    }
}
