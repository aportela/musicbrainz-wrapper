# musicbrainz-wrapper
Custom musicbrainz api wrapper

## Install (composer) dependencies:

```
composer require aportela/musicbrainz-wrapper
composer require monolog/monolog
```

# Code example:

```
<?php

    require "vendor/autoload.php";

    $logger = new \Monolog\Logger("musicbrainz-log");

    $mbArtist = new \aportela\MusicBrainzWrapper\Artist($logger);

    echo $mbArtist->GETXML("ca891d65-d9b0-4258-89f7-e6ba29d83767");

    echo PHP_EOL;

    echo $mbArtist->GETJSON("ca891d65-d9b0-4258-89f7-e6ba29d83767");

    echo PHP_EOL;

    $mbRelease = new \aportela\MusicBrainzWrapper\Release($logger);

    echo $mbRelease->GETXML("723df70e-f79e-4602-8d1f-13cad619a6e8");

    echo PHP_EOL;

    echo $mbRelease->GETJSON("723df70e-f79e-4602-8d1f-13cad619a6e8");

    echo PHP_EOL;

    $mbRecording = new \aportela\MusicBrainzWrapper\Recording($logger);

    echo $mbRecording->GETXML("4fba6ee9-b49d-4503-ba34-7337ed2e972f");

    echo PHP_EOL;

    echo $mbRecording->GETJSON("4fba6ee9-b49d-4503-ba34-7337ed2e972f");
```