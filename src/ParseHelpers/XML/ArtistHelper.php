<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\XML;


class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(\SimpleXMLElement $element)
    {
        $this->mbId = (string) $element->attributes()->id;
        $this->type = isset($element->attributes()->type) ? (\aportela\MusicBrainzWrapper\ArtistType::fromString($element->attributes()->type) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE) : \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $this->name = (string) $element->children()->name;
        $this->country = isset($element->children()->country) ?  (!empty($country = $element->children()->country) ? mb_strtolower($country) : null) : null;
    }
}
