<?php

namespace Sunspikes\Tests\ClamavValidator;

use Mockery;
use Illuminate\Validation\Factory;
use Illuminate\Support\Str;

class ValidateServiceProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testBoot()
    {
        $translator = Mockery::mock('\Illuminate\Contracts\Translation\Translator');
        $translator->shouldReceive('get');
        $translator->shouldReceive('addNamespace');

        $presence = Mockery::mock('\Illuminate\Validation\PresenceVerifierInterface');

        $factory = new Factory($translator);
        $factory->setPresenceVerifier($presence);

        $container = Mockery::mock('\Illuminate\Container\Container');
        $container->shouldReceive('bind');
        $container->shouldReceive('loadTranslationsFrom');
        $container->shouldReceive('offsetGet')->with('translator')->andReturn($translator);
        $container->shouldReceive('offsetGet')->with('validator')->andReturn($factory);

        $sp = Mockery::mock('\Sunspikes\ClamavValidator\ClamavValidatorServiceProvider[package]', array($container));
        $sp->boot();

        $validator = $factory->make(array(), array());

        foreach ($validator->getExtensions() as $rule => $class_and_method) {

            $class_and_method = "\\" . $class_and_method;

            $this->assertTrue(in_array($rule, $sp->getRules()));
            $this->assertEquals('\Sunspikes\ClamavValidator\ClamavValidator@validate' . studly_case($rule), $class_and_method);

            list($class, $method) = Str::parseCallback($class_and_method, null);

            $this->assertTrue(method_exists($class, $method));
        }
    }

    public function tearDown()
    {
        Mockery::close();
    }

}
