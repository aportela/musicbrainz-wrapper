<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper;

enum CoverArtArchiveImageSize: int
{
    case SMALL = 250;
    case NORMAL = 500;
    case LARGE = 1200;
}
