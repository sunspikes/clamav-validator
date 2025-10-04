<?php

namespace Sunspikes\ClamavValidator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Sunspikes\ClamavValidator\Rules\ClamAv;

class ClamavValidatorServiceProvider extends ServiceProvider
{
    /**
     * The list of validator rules.
     *
     * @var array
     */
    protected $rules = [
        'clamav' => ClamAv::class,
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'clamav-validator');

        $this->publishes([
            __DIR__ . '/../../config/clamav.php' => $this->app->configPath('clamav.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../lang' => $this->getLanguagePath(),
        ], 'lang');

        $this->addNewRules();
    }

    /**
     * Get the list of new rules being added to the validator.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Add new rules to the validator.
     */
    protected function addNewRules()
    {
        foreach ($this->getRules() as $token => $rule) {
            $this->extendValidator($token, $rule);
        }
    }

    /**
     * Extend the validator with new rules.
     *
     * @param string $token
     * @param string $rule
     *
     * @return void
     */
    protected function extendValidator(string $token, string $rule)
    {
        $translation = $this->app['translator']->get('clamav-validator::validation');

        $this->app['validator']->extend(
            $token,
            $rule . '@validate',
            $translation[$token] ?? []
        );
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/clamav.php', 'clamav');
    }

    /**
     * Get the language path for the validator.
     *
     * This method provides compatibility across multiple Laravel versions.
     *
     * @return string
     */
    protected function getLanguagePath(): string
    {
        // For Laravel 9+
        if (function_exists('lang_path')) {
            return lang_path('vendor/clamav-validator');
        }

        // For Laravel 5.3 - 8.x
        if (function_exists('resource_path')) {
            return resource_path('lang/vendor/clamav-validator');
        }

        // For Laravel 5.0 - 5.2
        return $this->app->basePath() . '/resources/lang/vendor/clamav-validator';
    }
}
