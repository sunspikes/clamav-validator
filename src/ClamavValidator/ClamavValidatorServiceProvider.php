<?php

namespace Sunspikes\ClamavValidator;

use Illuminate\Support\ServiceProvider;
use Sunspikes\ClamavValidator\Rules\ClamAv;

class ClamavValidatorServiceProvider extends ServiceProvider
{
    protected array $rules = [
        'clamav' => ClamAv::class,
    ];

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'clamav-validator');

        $this->publishes([
            __DIR__ . '/../../config/clamav.php' => $this->app->configPath('clamav.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../lang' => lang_path('vendor/clamav-validator'),
        ], 'lang');

        $this->addNewRules();
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    protected function addNewRules(): void
    {
        foreach ($this->getRules() as $token => $rule) {
            $this->extendValidator($token, $rule);
        }
    }

    protected function extendValidator(string $token, string $rule): void
    {
        $translation = $this->app['translator']->get('clamav-validator::validation');

        $this->app['validator']->extend(
            $token,
            $rule . '@validate',
            $translation[$token] ?? []
        );
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/clamav.php', 'clamav');
    }
}
