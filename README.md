# musicbrainz-wrapper

Custom musicbrainz api wrapper

## Requirements

- mininum php version 8.4
- curl extension must be enabled (aportela/httprequest-wrapper)

## Install (composer) dependencies:

```Shell
composer require aportela/musicbrainz-wrapper
```

## Classes:

<details>
<summary>\aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper</summary>

#### Description:

A [recording](https://wiki.musicbrainz.org/Recording) is an entity in MusicBrainz which can be linked to tracks on releases. Each track must always be associated with a single recording, but a recording can be linked to any number of tracks.

#### Properties:

- mbId (string)
- title (string)
- artistCredit (array of \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper)
</details>

<details>
<summary>\aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper</summary>

#### Description:

In MusicBrainz, a [track](https://wiki.musicbrainz.org/Track) is the way a recording is represented on a particular release (or, more exactly, on a particular medium). Every track has a title (see the guidelines for titles) and is credited to one or more artists. Tracks are additionally assigned MBIDs, though they cannot be the target of Relationships or other properties conventionally available to entities.

#### Properties:

- mbId (string)
- position (int)
- number (int)
- recording (\aportela\MusicBrainzWrapper\ParseHelpers\RecordingHelper)
</details>

<details>
<summary>\aportela\MusicBrainzWrapper\ParseHelpers\MediaHelper</summary>

#### Description:

In MusicBrainz terminology, a prototypical [medium](https://wiki.musicbrainz.org/Medium) is one of the physical, separate things you would get when you buy something in a record store. They are the individual CDs, vinyls, etc. contained within the packaging of an album (or any other type of release). Mediums are always included in a release, and have a position in said release (e.g. disc 1 or disc 2). They have a format, like CD, 12" vinyl or cassette (in some cases this will be unknown), and can have an optional title (e.g. disc 2: The Early Years).

#### Properties:

- mbId (string)
- position (int)
- trackList (array of \aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper)
</details>

<details>
<summary>\aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper</summary>

#### Description:

A MusicBrainz [release](https://wiki.musicbrainz.org/Release) represents the unique release (i.e. issuing) of a product containing at least one audio medium (a disc, for example, on a CD release). Each release has one or more identifying properties, such as a release date and country, a label, a barcode, a specific type of packaging or a specific cover art.

#### Properties:

- mbId (string)
- title (string)
- year (int|null)
- coverArtArchive (object)
  - **Properties:**
    - artwork (boolean)
    - front (boolean)
    - back (boolean)
- trackList (array of \aportela\MusicBrainzWrapper\ParseHelpers\TrackHelper)
- media (array of \aportela\MusicBrainzWrapper\ParseHelpers\MediaHelper)
</details>

<details>
<summary>\aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper</summary>

#### Description:

An [artist](https://wiki.musicbrainz.org/Artist) is generally a musician (or musician persona), group of musicians, or other music professional (like a producer or engineer). Occasionally, it can also be a non-musical person (like a photographer, an illustrator, or a poet whose writings are set to music), or even a fictional character.

#### Properties:

- mbId (string)
- type (\aportela\MusicBrainzWrapper\ArtistType)
- name (string)
- country (string|null)
- genres (array of string)
- relations (array of \aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper)
</details>

## Code example:

```php
<?php

    require "vendor/autoload.php";

    $logger = new \Psr\Log\NullLogger();

    /**
     *  NOTE regarding \aportela\MusicBrainzWrapper\Entity constructor params
     *  $logger => the log handler
     *  $apiFormat => json/xml
     *  $throttleDelayMS => milliseconds between consecutive MusicBrainz getEntity API calls (please use a reasonable value to avoid overloading the servers, ex: > 250ms)
     *  $cachePath => disk cache path, useful when we are continuously testing with the same values and don't want to overload the servers with repeated requests.
     */

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
