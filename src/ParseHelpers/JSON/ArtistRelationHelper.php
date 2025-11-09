<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;

class ArtistRelationHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistRelationHelper
{
    public function __construct(object $object)
    {
        $this->typeId = (string)($object->{"type-id"} ?? null);
        $this->type = (string)($object->type ?? null);
        $this->url = (string)($object->url->resource ?? null);
    }
}
