<?php

namespace Sunspikes\ClamavValidator\Rules;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Socket\Raw\Factory as SocketFactory;
use Sunspikes\ClamavValidator\ClamavValidatorException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Xenolope\Quahog\Client as QuahogClient;

class ClamAv
{
    public function validate(string $attribute, mixed $value, array $parameters): bool
    {
        if (filter_var(Config::get('clamav.skip_validation'), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (is_array($value)) {
            $result = true;
            foreach ($value as $file) {
                $result &= $this->validateFileWithClamAv($file);
            }

            return (bool) $result;
        }

        return $this->validateFileWithClamAv($value);
    }

    protected function validateFileWithClamAv(mixed $value): bool
    {
        $file = $this->getFilePath($value);
        if (!is_readable($file)) {
            throw ClamavValidatorException::forNonReadableFile($file);
        }

        try {
            $socket = $this->getClamavSocket();
            $scanner = $this->createQuahogScannerClient($socket);
            $result = $scanner->scanResourceStream(fopen($file, 'rb'));
        } catch (Exception $exception) {
            if (Config::get('clamav.client_exceptions')) {
                throw ClamavValidatorException::forClientException($exception);
            }
            return false;
        }

        if ($result->isError()) {
            if (Config::get('clamav.client_exceptions')) {
                throw ClamavValidatorException::forScanResult($result);
            }
            return false;
        }

        return $result->isOk();
    }

    protected function getClamavSocket(): string
    {
        $preferredSocket = Config::get('clamav.preferred_socket');

        if ($preferredSocket === 'unix_socket') {
            $unixSocket = Config::get('clamav.unix_socket');
            if (file_exists($unixSocket)) {
                return 'unix://' . $unixSocket;
            }
        }

        return Config::get('clamav.tcp_socket');
    }

    protected function getFilePath(UploadedFile|array|string $file): string
    {
        if ($file instanceof UploadedFile) {
            return $file->getRealPath();
        }

        if (is_array($file) && Arr::get($file, 'tmp_name') !== null) {
            return $file['tmp_name'];
        }

        return $file;
    }

    protected function createQuahogScannerClient(string $socket): QuahogClient
    {
        $client = (new SocketFactory())->createClient($socket, Config::get('clamav.socket_connect_timeout'));

        return new QuahogClient($client, Config::get('clamav.socket_read_timeout'), PHP_NORMAL_READ);
    }
}
