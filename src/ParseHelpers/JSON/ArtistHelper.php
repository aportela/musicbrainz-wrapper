<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)($object->id ?? null);
        $this->type = \aportela\MusicBrainzWrapper\ArtistType::fromString((string)($object->type ?? null));
        $this->name = (string)($object->name ?? null);
        $this->country = isset($object->country) ? (empty($country = $object->country) ? null : mb_strtolower((string) $country)) : null;
        if (isset($object->genres) && is_array(($object->genres))) {
            foreach ($object->genres as $genre) {
                if (is_object($genre) && isset($genre->name)) {
                    $this->genres[] = mb_strtolower(mb_trim($genre->name));
                }
            }
            $this->genres = array_unique($this->genres);
        }
        if (isset($object->relations) && is_array($object->relations)) {
            foreach ($object->relations as $artistRelation) {
                if (is_object($artistRelation)) {
                    $this->relations[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistRelationHelper($artistRelation);
                }
            }
        }
    }
}
