<?php

namespace Sunspikes\ClamavValidator;

use Exception;

class ClamavValidatorException extends Exception
{
    /**
     * @param string $file
     */
    public static function forNonReadableFile($file)
    {
        return new self(
            sprintf('The file "%s" is not readable', $file)
        );
    }

    /**
     * @param array $result
     */
    public static function forScanResult($result)
    {
        return new self(
            sprintf(
                'ClamAV scanner failed to scan file "%s" with error "%s" (%s)',
                $result['filename'],
                $result['reason'],
                $result['status']
            )
        );
    }

    /**
     * @param \Exception $exception
     */
    public static function forClientException($exception)
    {
        return new self(
            sprintf('ClamAV scanner client failed with error "%s"', $exception->getMessage()),
            0,
            $exception
        );
    }
}
