<?php

declare(strict_types=1);

namespace aportela\MusicBrainzWrapper\Exception;

class RemoteAPIServerConnectionException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
