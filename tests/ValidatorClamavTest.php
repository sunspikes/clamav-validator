<?php

namespace Sunspikes\Tests\ClamavValidator;

use Mockery;
use Illuminate\Contracts\Translation\Translator;
use Sunspikes\ClamavValidator\ClamavValidator;
use Sunspikes\ClamavValidator\ClamavValidatorException;

class ValidatorClamavTest extends \PHPUnit_Framework_TestCase
{
    protected $translator;
    protected $clean_data;
    protected $virus_data;
    protected $error_data;
    protected $rules;
    protected $messages;

    public function setUp()
    {
        $this->translator = Mockery::mock(Translator::class);
        $this->translator->shouldReceive('trans');
        $this->clean_data = [
            'file' => $this->getTempPath(__DIR__ . '/files/test1.txt')
        ];
        $this->virus_data = [
            'file' => $this->getTempPath(__DIR__ . '/files/test2.txt')
        ];
        $this->error_data = [
            'file' => $this->getTempPath(__DIR__ . '/files/test3.txt')
        ];
        $this->messages = [];
    }

    public function tearDown()
    {
        chmod($this->error_data['file'], 0644);
        Mockery::close();
    }

    public function testValidatesClean()
    {
        $validator = new ClamavValidator(
            $this->translator,
            $this->clean_data,
            ['file' => 'clamav'],
            $this->messages
        );

        $this->assertTrue($validator->passes());
    }

    public function testValidatesVirus()
    {
        $validator = new ClamavValidator(
            $this->translator,
            $this->virus_data,
            ['file' => 'clamav'],
            $this->messages
        );

        $this->assertFalse($validator->passes());
    }

    public function testValidatesError()
    {
        $validator = new ClamavValidator(
            $this->translator,
            $this->error_data,
            ['file' => 'clamav'],
            $this->messages
        );

        chmod($this->error_data['file'], 0000);

        $this->setExpectedException(ClamavValidatorException::class, 'Access denied.');

        $validator->passes();
    }

    /**
     * Move to temp dir, so that clamav can access the file
     *
     * @param $file
     * @return string
     */
    private function getTempPath($file)
    {
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($file);
        copy($file, $tempPath);
        chmod($tempPath, 0644);

        return $tempPath;
    }
}