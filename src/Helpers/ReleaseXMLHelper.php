<?php

namespace aportela\MusicBrainzWrapper\Helpers;

class ReleaseXMLHelper extends XMLHelper
{

    public function __construct(string $raw)
    {
        parent::__construct($raw);
    }

    public function parseSearchResponse(): array
    {
        $xpath = "//" . $this->getNS() . ":release-list/" . $this->getNS() . ":release";
        $releasesXPath = $this->getXPath($xpath);
        if ($releasesXPath === false) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidXMLException("xpath not found: " . $xpath);
        }
        $results = [];
        if (count($releasesXPath) > 0) {
            foreach ($releasesXPath as $releaseXPath) {
                $year = (string) $releaseXPath->children()->date;
                $year = strlen($year) == 10 ? intval(date_format(date_create_from_format('Y-m-d', $year), 'Y')) : (strlen($year) == 4 ? intval($year) : null);
                $artist = (object) [
                    "mbId" => null,
                    "name" => null,
                    "type" => \aportela\MusicBrainzWrapper\ArtistType::NONE,
                ];
                $results[] = (object) [
                    "mbId" => (string) $releaseXPath->attributes()->id,
                    "title" => (string) $releaseXPath->children()->title,
                    "year" => $year,
                    //"artist" => $artist
                ];
            }
        }
        return ($results);


        if ($xml->{"release-list"} && isset($xml->{"release-list"}['count']) && intval($xml->{"release-list"}['count']) > 0) {
            foreach ($xml->{"release-list"}->{"release"} as $release) {
                $releaseDate = isset($release->{"date"}) && !empty($release->{"date"}) ? $release->{"date"} : "";
                $results[] = (object) [
                    "mbId" => isset($release["id"]) ? (string) $release["id"] : null,
                    "title" => isset($release->{"title"}) ? (string) $release->{"title"} : null,
                    "year" => strlen($releaseDate) == 10 ? intval(date_format(date_create_from_format('Y-m-d', $releaseDate), 'Y')) : (strlen($releaseDate) == 4 ? intval($release->{"date"}) : null),
                    "artist" => (object) [
                        "mbId" => isset($release->{"artist-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}->artist) ? (string) $release->{"artist-credit"}->{"name-credit"}->artist["id"] : null,
                        "name" => isset($release->{"artist-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}) && isset($release->{"artist-credit"}->{"name-credit"}->artist) ? (string) $release->{"artist-credit"}->{"name-credit"}->artist->name : null
                    ]
                ];
            }
        } else {
            throw new \aportela\MusicBrainzWrapper\Exception\NotFoundException($title, $response->code);
        }
    }

    public function parseGetResponse() {}
}
