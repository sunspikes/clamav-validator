<?php

namespace Sunspikes\ClamavValidator;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Validator;
use Xenolope\Quahog\Client as QuahogClient;
use Socket\Raw\Factory as SocketFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamavValidator extends Validator
{
    /**
     * Creates a new instance of ClamavValidator.
     *
     * ClamavValidator constructor.
     * @param Translator $translator
     * @param array      $data
     * @param array      $rules
     * @param array      $messages
     * @param array      $customAttributes
     */
    public function __construct(
        Translator $translator,
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ) {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
    }

    /**
     * Validate the uploaded file for virus/malware with ClamAV.
     *
     * @param  $attribute   string
     * @param  $value       mixed
     * @param  $parameters  array
     *
     * @return boolean
     * @throws ClamavValidatorException
     */
    public function validateClamav($attribute, $value, $parameters)
    {
        if (true === Config::get('clamav.skip_validation')) {
            return true;
        }

        if(is_array($value)) {
        	$result = TRUE;    /* Assume all will be well */
        	foreach($value as $file) {
        		$result &= $this->validateFileWithClamAv($file);
			}

        	return $result;
		} else {
        	return $this->validateFileWithClamAv($value);
		}
	}

	/**
	 * Validate the single uploaded file for virus/malware with ClamAV.
	 *
	 * @param $value mixed
	 *
	 * @return bool
	 * @throws ClamavValidatorException
	 */
	protected function validateFileWithClamAv($value)
	{
        $file = $this->getFilePath($value);
        if (! is_readable($file)) {
            throw ClamavValidatorException::forNonReadableFile($file);
        }

        try {
            $socket  = $this->getClamavSocket();
            $scanner = $this->createQuahogScannerClient($socket);
            $result  = $scanner->scanResourceStream(fopen($file, 'rb'));
        } catch (\Exception $exception) {
            throw ClamavValidatorException::forClientException($exception);
        }

        if (QuahogClient::RESULT_ERROR === $result['status']) {
            throw ClamavValidatorException::forScanResult($result);
        }

        // Check if scan result is clean
        return QuahogClient::RESULT_OK === $result['status'];
    }

    /**
     * Guess the ClamAV socket.
     *
     * @return string
     */
    protected function getClamavSocket()
    {
        $preferredSocket = Config::get('clamav.preferred_socket');

        if ($preferredSocket === 'unix_socket') {
            $unixSocket = Config::get('clamav.unix_socket');
            if (file_exists($unixSocket)) {
                return 'unix://' . $unixSocket;
            }
        }

        // We use the tcp_socket as fallback as well
        return Config::get('clamav.tcp_socket');
    }

    /**
     * Return the file path from the passed object.
     *
     * @param mixed $file
     * @return string
     */
    protected function getFilePath($file)
    {
        // if were passed an instance of UploadedFile, return the path
        if ($file instanceof UploadedFile) {
            return $file->getPathname();
        }

        // if we're passed a PHP file upload array, return the "tmp_name"
        if (is_array($file) && null !== Arr::get($file, 'tmp_name')) {
            return $file['tmp_name'];
        }

        // fallback: we were likely passed a path already
        return $file;
    }

    /**
     * Create a new quahog ClamAV scanner client.
     *
     * @param string $socket
     * @return QuahogClient
     */
    protected function createQuahogScannerClient($socket)
    {
        // Create a new client socket instance
        $client = (new SocketFactory())->createClient($socket);

        return new QuahogClient($client, Config::get('clamav.socket_read_timeout'), PHP_NORMAL_READ);
    }
}
