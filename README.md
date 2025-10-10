# musicbrainz-wrapper

Custom musicbrainz api wrapper

## Requirements

- mininum php version 8.4
- curl extension must be enabled (aportela/httprequest-wrapper)

## Install (composer) dependencies:

```
composer require aportela/musicbrainz-wrapper
```

## Code example:

```
<?php

    require "vendor/autoload.php";

    $logger = new \Psr\Log\NullLogger();

    // JSON format
    $mbArtist = new \aportela\MusicBrainzWrapper\Artist($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
    // get artist object from MusicBrainz API
    $mbArtist->get("ca891d65-d9b0-4258-89f7-e6ba29d83767");
    // parse raw json string (use this if you have an stored pre-cached json music brainz artist api response) into artist object
    $mbArtist->parse($mbArtist->raw);
    // get youtube url artist relationships
    $urls = $mbArtist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::SOCIAL_YOUTUBE);

    // XML format
    $mbArtist = new \aportela\MusicBrainzWrapper\Artist($logger, \aportela\MusicBrainzWrapper\APIFormat::XML);
    // get artist object from MusicBrainz API
    $mbArtist->get("ca891d65-d9b0-4258-89f7-e6ba29d83767");
    // parse raw xml string (use this if you have an stored pre-cached xml music brainz artist api response) into artist object
    $mbArtist->parse($mbArtist->raw);
    // get youtube url artist relationships
    $urls = $mbArtist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::SOCIAL_YOUTUBE);

    // JSON format
    $mbRelease = new \aportela\MusicBrainzWrapper\Release($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
    // get release object from MusicBrainz API
    $mbRelease->get("723df70e-f79e-4602-8d1f-13cad619a6e8");
    // parse raw json string (use this if you have an stored pre-cached json music brainz release api response) into release object
    $mbRelease->parse($mbRelease->raw);

    // XML format
    $mbRelease = new \aportela\MusicBrainzWrapper\Release($logger, \aportela\MusicBrainzWrapper\APIFormat::XML);
    // get release object from MusicBrainz API
    $mbRelease->get("723df70e-f79e-4602-8d1f-13cad619a6e8");
    // parse raw xml string (use this if you have an stored pre-cached xml music brainz release api response) into release object
    $mbRelease->parse($mbRelease->raw);

    // JSON format
    $mbRecording = new \aportela\MusicBrainzWrapper\Recording($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
    // get recording object from MusicBrainz API
    $mbRecording->get("4fba6ee9-b49d-4503-ba34-7337ed2e972f");
    // parse raw json string (use this if you have an stored pre-cached json music brainz recording api response) into recording object
    $mbRecording->parse($mbRecording->raw);

    // XML format
    $mbRecording = new \aportela\MusicBrainzWrapper\Recording($logger, \aportela\MusicBrainzWrapper\APIFormat::XML);
    // get recording object from MusicBrainz API
    $mbRecording->get("4fba6ee9-b49d-4503-ba34-7337ed2e972f");
    // parse raw xml string (use this if you have an stored pre-cached json music brainz recording api response) into recording object
    $mbRecording->parse($mbRecording->raw);
```

![PHP Composer](https://github.com/aportela/musicbrainz-wrapper/actions/workflows/php.yml/badge.svg)
