<?php

namespace Sunspikes\Tests\ClamavValidator;

use Mockery;
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
        $this->translator = Mockery::mock('\Illuminate\Contracts\Translation\Translator');
        $this->translator->shouldReceive('trans');
        $this->clean_data = array(
            'file' => dirname(__FILE__) . '/files/test1.txt'
        );
        $this->virus_data = array(
            'file' => dirname(__FILE__) . '/files/test2.txt'
        );
        $this->error_data = array(
            'file' => dirname(__FILE__) . '/files/test3.txt'
        );
        $this->messages = array();
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
            array('file' => 'clamav'),
            $this->messages
        );

        $this->assertTrue($validator->passes());
    }

    public function testValidatesVirus()
    {
        $validator = new ClamavValidator(
            $this->translator,
            $this->virus_data,
            array('file' => 'clamav'),
            $this->messages
        );

        $this->assertFalse($validator->passes());
    }

    public function testValidatesError()
    {
        $validator = new ClamavValidator(
            $this->translator,
            $this->error_data,
            array('file' => 'clamav'),
            $this->messages
        );

        chmod($this->error_data['file'], 0000);

        $this->setExpectedException('\Sunspikes\ClamavValidator\ClamavValidatorException', 'Access denied.');

        $validator->passes();
    }
}