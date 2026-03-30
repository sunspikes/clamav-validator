<?php

namespace Sunspikes\Tests\ClamavValidator\Helpers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;
use Mockery;
use Sunspikes\ClamavValidator\Rules\ClamAv;

trait ValidatorHelper
{
    public function makeValidator(array $data, array $rules, ?Translator $translator = null, array $messages = []): Validator
    {
        $translator = $translator ?? $this->makeMockedTranslator();
        $messages = !empty($messages) ? $messages : $this->defaultErrorMessages();

        $factory = new Factory($translator, Container::getInstance());

        foreach ($this->rules() as $token => $rule) {
            $factory->extend(
                $token,
                $rule . '@validate',
                $messages
            );
        }

        return $factory->make($data, $rules);
    }

    protected function rules(): array
    {
        return [
            'clamav' => ClamAv::class,
        ];
    }

    protected function makeMockedTranslator(): Translator
    {
        $translator = Mockery::mock(Translator::class);

        $translator
            ->shouldReceive('get')
            ->with('validation.custom.file.clamav')
            ->andReturn('error');

        $translator
            ->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn(null);

        $translator
            ->shouldReceive('get')
            ->with('validation.attributes')
            ->andReturn([]);

        return $translator;
    }

    protected function defaultErrorMessages(): array
    {
        return [
            'clamav' => ':attribute contains virus.'
        ];
    }

    protected function getTempPath(string $file): string
    {
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($file);
        copy($file, $tempPath);
        chmod($tempPath, 0644);

        return $tempPath;
    }
}
