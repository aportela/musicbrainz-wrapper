<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers\JSON;


class ArtistHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper
{
    public function __construct(object $element)
    {
        $this->mbId = (string)$element->{"id"};
        $this->type = \aportela\MusicBrainzWrapper\ArtistType::fromString($element->{"type"}) ?: \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $this->name = (string)$element->{"name"};
        $this->country = isset($element->{"country"}) ? (!empty($country = $element->{"country"}) ? mb_strtolower($country) : null) : null;
    }
}
