<?php namespace Sunspikes\ClamavValidator;

use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamavValidator extends Validator
{
	/** 
	 * @const string CLAMAV_STATUS_OK
	 */
	const CLAMAV_STATUS_OK = 'OK';
	
	/** 
	 * @const string CLAMAV_UNIX_SOCKET
	 */
	const CLAMAV_UNIX_SOCKET = 'unix:///var/run/clamav/clamd.ctl';

	/**
	 * Creates a new instance of ClamavValidator
	 */
	public function __construct($translator, $data, $rules, $messages)
	{
		parent::__construct($translator, $data, $rules, $messages);
	}

	/**
	 * Validate the uploaded file for virus/malware with ClamAV
	 *
	 * @param  $attribute  string
	 * @param  $value 	   mixed
	 * @param  $parameters array
	 * @return boolean
	 */
	public function validateClamav($attribute, $value, $parameters)
	{
		$file = $this->getFilePath($value);

		// Create a new socket instance
		$socket = (new \Socket\Raw\Factory())->createClient(self::CLAMAV_UNIX_SOCKET);
		
		// Create a new instance of the Client
		$quahog = new \Quahog\Client($socket);
		
		// Scan the file
		$result = $quahog->scanFile($file);
		
		// Check if scan result is not clean
		if (self::CLAMAV_STATUS_OK != $result['status']) 
		{
			return false;
		}

		return true;
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
		if ($file instanceof UploadedFile)
		{
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
