<?php namespace Sunspikes\ClamavValidator;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Validator;
use Xenolope\Quahog\Client;
use Socket\Raw\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamavValidator extends Validator
{
    /**
     * @const string CLAMAV_STATUS_OK
     */
    const CLAMAV_STATUS_OK = 'OK';

    /**
     * @const string CLAMAV_STATUS_ERROR
     */
    const CLAMAV_STATUS_ERROR = 'ERROR';

    /**
     * @const string CLAMAV_UNIX_SOCKET
     */
    const CLAMAV_UNIX_SOCKET = '/var/run/clamav/clamd.ctl';

    /**
     * @const string CLAMAV_LOCAL_TCP_SOCKET
     */
    const CLAMAV_LOCAL_TCP_SOCKET = 'tcp://127.0.0.1:3310';

    /**
     * @const string CLAMAV_SOCKET_READ_TIMEOUT
     */
    const CLAMAV_SOCKET_READ_TIMEOUT = 30;

    /**
     * Creates a new instance of ClamavValidator]
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
     * Validate the uploaded file for virus/malware with ClamAV
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
        $file = $this->getFilePath($value);
        $clamavSocket = $this->getClamavSocket();

        // Create a new socket instance
        $socket = (new Factory())->createClient($clamavSocket);

        // Create a new instance of the Client
        $quahog = new Client($socket, self::CLAMAV_SOCKET_READ_TIMEOUT, PHP_NORMAL_READ);

        // Check if the file is readable
        if (! is_readable($file)) {
            throw new ClamavValidatorException(sprintf('The file "%s" is not readable', $file));
        }

        // Scan the file
        $result = $quahog->scanResourceStream(fopen($file, 'rb'));

        if (self::CLAMAV_STATUS_ERROR === $result['status']) {
            throw new ClamavValidatorException($result['reason']);
        }

        // Check if scan result is not clean
        return self::CLAMAV_STATUS_OK === $result['status'];
    }

    /**
     * Guess the ClamAV socket
     *
     * @return string
     */
    protected function getClamavSocket()
    {
        if (file_exists(env('CLAMAV_UNIX_SOCKET', self::CLAMAV_UNIX_SOCKET))) {
            return 'unix://' . env('CLAMAV_UNIX_SOCKET', self::CLAMAV_UNIX_SOCKET);
        }

        return env('CLAMAV_TCP_SOCKET', self::CLAMAV_LOCAL_TCP_SOCKET);
    }

    /**
     * Return the file path from the passed object
     *
     * @param $file mixed
     * @return string
     */
    protected function getFilePath($file)
    {
        // if were passed an instance of UploadedFile, return the path
        if ($file instanceof UploadedFile) {
            return $file->getPathname();
        }

        // if we're passed a PHP file upload array, return the "tmp_name"
        if (is_array($file) && null !== array_get($file, 'tmp_name')) {
            return $file['tmp_name'];
        }

        // fallback: we were likely passed a path already
        return $file;
    }
}
