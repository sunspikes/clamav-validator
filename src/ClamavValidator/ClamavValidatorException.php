<?php

namespace Sunspikes\ClamavValidator;

use Exception;
use Xenolope\Quahog\Result;

class ClamavValidatorException extends Exception
{
    /**
     * @param string $file
     * @return ClamavValidatorException
     */
    public static function forNonReadableFile(string $file): ClamavValidatorException
    {
        return new self(
            sprintf('The file "%s" is not readable', $file)
        );
    }

    /**
     * @param Result $result
     * @return ClamavValidatorException
     */
    public static function forScanResult(Result $result): ClamavValidatorException
    {
        return new self(
            sprintf(
                'ClamAV scanner failed to scan file "%s" with error "%s"',
                $result->getFilename(),
                $result->getReason()
            )
        );
    }

    /**
     * @param Exception $exception
     * @return ClamavValidatorException
     */
    public static function forClientException(Exception $exception): ClamavValidatorException
    {
        return new self(
            sprintf('ClamAV scanner client failed with error "%s"', $exception->getMessage()),
            0,
            $exception
        );
    }
}
