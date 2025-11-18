<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper;

/**
 * https://musicbrainz.org/doc/Artist
 */
enum ArtistType: string
{
    case NONE = "None";
    case PERSON = "Person";
    case GROUP = "Group";
    case ORCHESTA = "Orchestra";
    case CHOIR = "Choir";
    case CHARACTER = "Character";
    case OTHER = "Other";

    public function toInt(): int
    {
        return match ($this) {
            self::NONE => 0,
            self::PERSON => 1,
            self::GROUP => 2,
            self::ORCHESTA => 3,
            self::CHOIR => 4,
            self::CHARACTER => 5,
            self::OTHER => 6,
        };
    }

    public static function fromInt(int $value): ArtistType
    {
        return match ($value) {
            0 => self::NONE,
            1 => self::PERSON,
            2 => self::GROUP,
            3 => self::ORCHESTA,
            4 => self::CHOIR,
            5 => self::CHARACTER,
            6 => self::OTHER,
            default => self::NONE,
        };
    }
}
