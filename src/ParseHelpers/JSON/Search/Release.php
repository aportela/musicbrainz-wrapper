<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON\Search;

class Release extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseJSONHelper
{
    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\JSON\ReleaseHelper>
     */
    public function parse(): array
    {
        if (! (property_exists($this->json, "count") && property_exists($this->json, "releases"))) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidJSONException("artists count/array not found");
        }

        $results = [];
        if (is_numeric($this->json->count) && intval($this->json->count) > 0 &&  property_exists($this->json, "releases") && is_array($this->json->releases)) {
            foreach ($this->json->releases as $releaseObject) {
                if (is_object($releaseObject)) {
                    $results[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ReleaseHelper($releaseObject);
                }
            }
        }

        return ($results);
    }
}
