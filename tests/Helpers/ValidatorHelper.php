<?php

namespace Sunspikes\Tests\ClamavValidator\Helpers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;
use Mockery;
use Sunspikes\ClamavValidator\Rules\ClamAv;

/**
 * Validator Tests Helper
 */
trait ValidatorHelper
{
    /**
     * Creates a new validator instance
     *
     * @param array $data
     * @param array $rules
     * @param Translator|null $translator [optional]
     * @param array $messages [optional]
     *
     * @return Validator
     */
    public function makeValidator(array $data, array $rules, $translator = null, array $messages = [])
    {
        // Resolve translator and error messages, when none given
        $translator = $translator ?? $this->makeMockedTranslator();
        $messages = !empty($messages)
            ? $messages
            : $this->defaultErrorMessages();

        // Create new Laravel Validator factory instance and install extensions (custom rules)
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

    /**
     * Returns validation rules to installed in validator
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'clamav' => ClamAv::class,
        ];
    }

    /**
     * Returns a new mocked {@see Translator} instance
     *
     * @return Translator|Translator&Mockery\LegacyMockInterface|Translator&Mockery\MockInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected function makeMockedTranslator()
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

    /**
     * Returns a set of default error messages
     *
     * @return string[]
     */
    protected function defaultErrorMessages(): array
    {
        return [
            'clamav' => ':attribute contains virus.'
        ];
    }

    /**
     * Move to temp dir, so that clamav can access the file
     *
     * @param string $file
     *
     * @return string
     */
    protected function getTempPath($file): string
    {
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($file);
        copy($file, $tempPath);
        chmod($tempPath, 0644);

        return $tempPath;
    }
}