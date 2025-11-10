<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ArtistRelationHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistRelationHelper
{
    public function __construct(object $object)
    {
        $this->typeId = property_exists($object, "type-id") && is_string($object->{"type-id"}) ? $object->{"type-id"} : "";
        $this->type = property_exists($object, "type") && is_string($object->type) ? $object->type : "";
        $this->url = property_exists($object, "url") && is_object($object->url) && property_exists($object->url, "resource") && is_string($object->url->resource) ? $object->url->resource : "";
    }
}
