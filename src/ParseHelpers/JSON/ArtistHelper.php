<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)$object->id;
        $this->type = \aportela\MusicBrainzWrapper\ArtistType::fromString((string)($object->type ?? null));
        $this->name = (string)$object->name;
        $this->country = isset($object->country) ? (!empty($country = $object->country) ? mb_strtolower($country) : null) : null;
        if (isset($object->genres) && is_array(($object->genres))) {
            foreach ($object->{"genres"} as $genre) {
                $this->genres[] = mb_strtolower(trim($genre->name));
            }
            $this->genres = array_unique($this->genres);
        }
        if (isset($object->relations) && is_array($object->relations)) {
            foreach ($object->relations as $artistRelation) {
                $this->relations[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistRelationHelper($artistRelation);
            }
        }
    }
}
