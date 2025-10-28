<?php

namespace aportela\MusicBrainzWrapper\ParseHelpers;

class BaseHelper
{
    public string $mbId;

    protected function parseDateToYear(?string $date): ?int
    {
        if ($date !== null) {
            switch (mb_strlen($date)) {
                case 10:
                    return (intval(date_format(date_create_from_format('Y-m-d', $date), 'Y')));
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
