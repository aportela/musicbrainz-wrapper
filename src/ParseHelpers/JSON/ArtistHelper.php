<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

use aportela\MusicBrainzWrapper\ArtistType;

class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(object $object)
    {
        $this->mbId = property_exists($object, "id") && is_string($object->id) ? $object->id : "";
        $this->type = property_exists($object, "type") && is_string($object->type) ? \aportela\MusicBrainzWrapper\ArtistType::fromString($object->type) : ArtistType::NONE;
        $this->name = property_exists($object, "name") && is_string($object->name) ? $object->name : "";
        $this->country = property_exists($object, "country") && is_string($object->country) && ($object->country !== '' && $object->country !== '0') ? mb_strtolower($object->country) : null;
        if (property_exists($object, "genres") && is_array(($object->genres))) {
            foreach ($object->genres as $genre) {
                if (is_object($genre) && property_exists($genre, "name") && is_string($genre->name)) {
                    $this->genres[] = mb_strtolower(mb_trim($genre->name));
                }
            }

            $this->genres = array_unique($this->genres);
        }

        if (property_exists($object, "relations") && is_array($object->relations)) {
            foreach ($object->relations as $artistRelation) {
                if (is_object($artistRelation)) {
                    $this->relations[] = new \aportela\MusicBrainzWrapper\ParseHelpers\JSON\ArtistRelationHelper($artistRelation);
                }
            }
        }
    }
}
