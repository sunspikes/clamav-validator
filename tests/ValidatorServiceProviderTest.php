<?php

namespace Sunspikes\Tests\ClamavValidator;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\PresenceVerifierInterface;
use Mockery;
use Illuminate\Validation\Factory;
use Illuminate\Support\Str;
use Sunspikes\ClamavValidator\ClamavValidator;
use Sunspikes\ClamavValidator\ClamavValidatorServiceProvider;

class ValidatorServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testBoot()
    {
        $translator = Mockery::mock(Translator::class);
        $translator->shouldReceive('get');
        $translator->shouldReceive('addNamespace');

        $presence = Mockery::mock(PresenceVerifierInterface::class);

        $factory = new Factory($translator);
        $factory->setPresenceVerifier($presence);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('bind');
        $container->shouldReceive('loadTranslationsFrom');
        $container->shouldReceive('offsetGet')->with('translator')->andReturn($translator);
        $container->shouldReceive('offsetGet')->with('validator')->andReturn($factory);

        $sp = Mockery::mock(ClamavValidatorServiceProvider::class .'[package]', [$container]);
        $sp->boot();

        $validator = $factory->make([], []);

        foreach ($validator->extensions as $rule => $class_and_method) {

            $class_and_method = "\\" . $class_and_method;

            $this->assertTrue(in_array($rule, $sp->getRules()));
            $this->assertEquals(ClamavValidator::class .'@validate' . studly_case($rule), $class_and_method);

            list($class, $method) = Str::parseCallback($class_and_method, null);

            $this->assertTrue(method_exists($class, $method));
        }
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
