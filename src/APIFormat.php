<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper;

enum APIFormat: string
{
    case JSON = "json";
    case XML = "xml";
}
