<?php

namespace aportela\MusicBrainzWrapper;

class ArtistBase extends \aportela\MusicBrainzWrapper\Entity
{

    public \aportela\MusicBrainzWrapper\ArtistType $type = \aportela\MusicBrainzWrapper\ArtistType::NONE;
    public ?string $name = null;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat, int $throttleDelayMS = 0, ?string $cachePath = null)
    {
        parent::__construct($logger, $apiFormat, $throttleDelayMS, $cachePath);
        $this->reset();
    }

    protected function reset(): void
    {
        parent::reset();
        $this->type = \aportela\MusicBrainzWrapper\ArtistType::NONE;
        $this->name = null;
    }
}
