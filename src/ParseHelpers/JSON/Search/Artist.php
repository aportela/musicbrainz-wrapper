<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search;

class Artist extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper>
     */
    public function parse(): array
    {
        if (! (isset($this->json->count) && isset($this->json->artists))) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidJSONException("artists count/array not found");
        }
        
        $results = [];
        if (isset($this->json->count) && intval($this->json->count) > 0 && is_array($this->json->artists)) {
            foreach ($this->json->artists as $artistObject) {
                if (is_object($artistObject)) {
                    $results[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistHelper($artistObject);
                }
            }
        }
        
        return ($results);
    }
}
