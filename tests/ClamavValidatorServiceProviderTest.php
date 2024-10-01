<?php

namespace Sunspikes\Tests\ClamavValidator;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
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
        $translator->shouldReceive('get')->with('clamav-validator::validation')->andReturn('error');
        $translator->shouldReceive('addNamespace');

        $presence = Mockery::mock(PresenceVerifierInterface::class);

        $factory = new Factory($translator);
        $factory->setPresenceVerifier($presence);

        /** @var Mockery\Mock|Application $container */
        $container = Mockery::mock(Container::class)->makePartial();
        $container->shouldReceive('offsetGet')->with('translator')->andReturn($translator);
        $container->shouldReceive('offsetGet')->with('validator')->andReturn($factory);
        $container->shouldReceive('configPath');
        $container->shouldReceive('resourcePath');

        Facade::setFacadeApplication($container);

        $serviceProvider = new ClamavValidatorServiceProvider($container);
        $serviceProvider->boot();

        $validator = $factory->make([], []);

        foreach ($validator->extensions as $rule => $class_and_method) {

            // Ensure rule exists in service provider ~ that validator has installed it
            $this->assertArrayHasKey($rule, $serviceProvider->getRules());

            // Ensure that validation rule's validate method can be invoked...
            list($class, $method) = Str::parseCallback($class_and_method);
            $this->assertTrue(method_exists($class, $method));
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
