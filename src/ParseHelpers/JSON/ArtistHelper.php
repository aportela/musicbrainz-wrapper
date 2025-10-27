<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;


class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(object $object)
    {
        $this->mbId = (string)$object->id;
        $this->type = isset($object->type) ? (\aportela\MusicBrainzWrapper\ArtistType::fromString($object->type) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE) : \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $this->name = (string)$object->name;
        $this->country = isset($object->country) ? (!empty($country = $object->country) ? mb_strtolower($country) : null) : null;
    }
}
