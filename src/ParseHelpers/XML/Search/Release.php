<?php

declare(strict_types=1);

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
        if (is_array($releasesXPath) && $releasesXPath !== []) {
            foreach ($releasesXPath as $releaseXPath) {
                $results[] = new \aportela\MusicBrainzWrapper\ParseHelpers\XML\ReleaseHelper($releaseXPath);
            }
        }

        return ($results);
    }
}
