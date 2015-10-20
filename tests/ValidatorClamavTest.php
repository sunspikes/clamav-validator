<?php

use Sunspikes\ClamavValidator\ClamavValidator;

class ValidatorClamavTest extends PHPUnit_Framework_TestCase
{
    protected $translator;
    protected $clean_data;
    protected $virus_data;
    protected $rules;
    protected $messages;


    public function setUp()
    {
        $this->translator = Mockery::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->translator->shouldReceive('trans');
        $this->clean_data = array(
            'file' => dirname(__FILE__) . '/files/test1.txt'
        );
        $this->virus_data = array(
            'file' => dirname(__FILE__) . '/files/test2.txt'
        );
        $this->messages = array();
    }


    public function tearDown()
    {
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
}