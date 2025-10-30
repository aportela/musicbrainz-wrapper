<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class ReleaseHelper extends \aportela\MusicBrainzWrapper\ParseHelpers\BaseHelper
{
    public string $title;
    public ?int $year = null;

    /**
     * @var array<\aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper>
     */
    public array $artistCredit = [];

    public object $coverArtArchive;

    /**
     * @var array<\aportela\MusicBrainzWrapper\ParseHelpers\MediaHelper>
     */
    public array $media = [];

    public function __construct()
    {
        $this->coverArtArchive = (object)
        [
            "artwork" => false,
            "front" => false,
            "back" => false
        ];
    }

    public function parseDateToYear(?string $date): ?int
    {
        if ($date !== null) {
            switch (mb_strlen(mb_trim($date))) {
                case 10:
                    $dateObj = date_create_from_format('Y-m-d', $date);
                    if ($dateObj !== false) {
                        return (intval(date_format($dateObj, 'Y')));
                    } else {
                        return (null);
                    }
                    // no break
                case 4:
                    return (intval($date));
                default:
                    return (null);
            }
        } else {
            return (null);
        }
    }
}
