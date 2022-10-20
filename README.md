# musicbrainz-wrapper
Custom musicbrainz api wrapper

## Install (composer) dependencies:

```
composer require aportela/musicbrainz-wrapper
composer require psr/log
```

# Code example:

```
<?php

    require "vendor/autoload.php";

    $logger = new \Psr\Log\NullLogger("");

    // get from MusicBrainz JSON API
    $mbArtist = new \aportela\MusicBrainzWrapper\Artist($logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
    echo $mbArtist->get("ca891d65-d9b0-4258-89f7-e6ba29d83767");
    echo PHP_EOL;

    // get from MusicBrainz XML API
    $mbArtist = new \aportela\MusicBrainzWrapper\Artist($logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML);
    echo $mbArtist->get("ca891d65-d9b0-4258-89f7-e6ba29d83767");
    echo PHP_EOL;

    // get from MusicBrainz JSON API
    $mbRelease = new \aportela\MusicBrainzWrapper\Release($logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
    echo $mbRelease->get("723df70e-f79e-4602-8d1f-13cad619a6e8");
    echo PHP_EOL;

    // get from MusicBrainz XML API
    $mbRelease = new \aportela\MusicBrainzWrapper\Release($logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML);
    echo $mbRelease->get("723df70e-f79e-4602-8d1f-13cad619a6e8");
    echo PHP_EOL;

    // get from MusicBrainz JSON API
    $mbRecording = new \aportela\MusicBrainzWrapper\Recording($logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
    echo $mbRecording->get("4fba6ee9-b49d-4503-ba34-7337ed2e972f");
    echo PHP_EOL;

    // get from MusicBrainz JSON API
    $mbRecording = new \aportela\MusicBrainzWrapper\Recording($logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_XML);
    echo $mbRecording->get("4fba6ee9-b49d-4503-ba34-7337ed2e972f");
    echo PHP_EOL;

```