<?php

namespace Sunspikes\ClamavValidator;

use Exception;
use Throwable;
use Xenolope\Quahog\Result;

class ClamavValidatorException extends Exception
{
    public static function forNonReadableFile(string $file): static
    {
        return new static(
            sprintf('The file "%s" is not readable', $file)
        );
    }

    public static function forScanResult(Result $result): static
    {
        return new static(
            sprintf(
                'ClamAV scanner failed to scan file "%s" with error "%s"',
                $result->getFilename(),
                $result->getReason()
            )
        );
    }

    public static function forClientException(Throwable $exception): static
    {
        return new static(
            sprintf('ClamAV scanner client failed with error "%s"', $exception->getMessage()),
            0,
            $exception
        );
    }
}
