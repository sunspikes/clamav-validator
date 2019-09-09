<?php

namespace Sunspikes\Tests\ClamavValidator;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\PresenceVerifierInterface;
use Mockery;
use Illuminate\Validation\Factory;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Sunspikes\ClamavValidator\ClamavValidator;
use Sunspikes\ClamavValidator\ClamavValidatorServiceProvider;

class ClamavValidatorServiceProviderTest extends TestCase
{
    public function testBoot()
    {
        $translator = Mockery::mock(Translator::class);
        $translator->shouldReceive('get');
        $translator->shouldReceive('addNamespace');

        $presence = Mockery::mock(PresenceVerifierInterface::class);

        $factory = new Factory($translator);
        $factory->setPresenceVerifier($presence);

        $config = Config::spy();

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('bind');
        $container->shouldReceive('loadTranslationsFrom');
        $container->shouldReceive('offsetGet')->with('translator')->andReturn($translator);
        $container->shouldReceive('offsetGet')->with('validator')->andReturn($factory);
        $container->shouldReceive('offsetGet')->with('config')->andReturn($config);
        $container->shouldReceive('configPath');
        $container->shouldReceive('resourcePath');

        Facade::setFacadeApplication($container);

        $sp = new ClamavValidatorServiceProvider($container);
        $sp->boot();

        $validator = $factory->make([], []);

        foreach ($validator->extensions as $rule => $class_and_method) {

            $this->assertTrue(in_array($rule, $sp->getRules()));
            $this->assertEquals(ClamavValidator::class .'@validate' . Str::studly($rule), $class_and_method);

            list($class, $method) = Str::parseCallback($class_and_method, null);

            $this->assertTrue(method_exists($class, $method));
        }
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
