<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search;

class Release extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\JSON\ReleaseHelper>
     */
    public function parse(): array
    {
        if (! (isset($this->json->count) && isset($this->json->releases))) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidJSONException("artists count/array not found");
        }
        
        $results = [];
        if (isset($this->json->count) && intval($this->json->count) > 0 && is_array($this->json->releases)) {
            foreach ($this->json->releases as $releaseObject) {
                if (is_object($releaseObject)) {
                    $results[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ReleaseHelper($releaseObject);
                }
            }
        }
        
        return ($results);
    }
}
