<?php

namespace aportela\MusicBrainzWrapper\Helpers;

class ReleaseJSONHelper extends XMLHelper
{

    public function __construct(string $raw)
    {
        parent::__construct($raw);
    }

    public function parseSearchResponse(): array
    {
        return ([]);
    }

    public function parseGetResponse() {}
}
