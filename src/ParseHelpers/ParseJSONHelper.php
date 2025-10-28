<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

abstract class ParseJSONHelper
{
    protected mixed $json;

    public function __construct(string $raw)
    {
        $this->json = json_decode($raw);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \aportela\MusicBrainzWrapper\Exception\InvalidJSONException(json_last_error_msg(), json_last_error());
        }
    }

    abstract public function parse(): mixed;
}
