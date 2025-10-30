<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML\Search;

class Release extends \aportela\MusicBrainzWrapper\ParseHelpers\ParseXMLHelper
{
    /**
     * @return array<\aportela\MusicBrainzWrapper\ParseHelpers\XML\ReleaseHelper>
     */
    public function parse(): array
    {
        $releasesXPath = $this->getXPath("//" . $this->getNS() . ":release-list/" . $this->getNS() . ":release");
        if ($releasesXPath === false) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("release-list xpath not found");
        }
        $results = [];
        if (is_array($releasesXPath) && count($releasesXPath) > 0) {
            foreach ($releasesXPath as $releaseElement) {
                $results[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ReleaseHelper($releaseElement);
            }
        }
        return ($results);
    }
}
