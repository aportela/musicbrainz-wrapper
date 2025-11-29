<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;

class ArtistRelationHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistRelationHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->typeId = (string) $element->attributes()->{"type-id"};
        $this->type = (string) $element->attributes()->type;
        $this->url = (string) $element->children()->target;
    }
}
