<?php namespace Sunspikes\ClamavValidator;

use Illuminate\Support\ServiceProvider;

class ClamavValidatorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * The list of validator rules.
	 *
	 * @var bool
	 */
	protected $rules = array(
		'clamav',
	);

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('sunspikes/clamav-validator', 'clamav-validator');

		$this->app->bind('Sunspikes\ClamavValidator\ClamavValidator', function($app)
		{
			$validator = new ClamavValidator($app['translator'], array(), array(), $app['translator']->get('clamav-validator::validation'));

			if (isset($app['validation.presence']))
			{
				$validator->setPresenceVerifier($app['validation.presence']);
			}

			return $validator;

		});

		$this->addNewRules();
	}

	/**
	 * Get the list of new rules being added to the validator.
	 *
	 * @return array
	 */
	public function getRules()
	{
		return $this->rules;
	}


	/**
	 * Add new rules to the validator.
	 */
	protected function addNewRules()
	{
		foreach($this->getRules() as $rule)
		{
			$this->extendValidator($rule);
		}
	}


	/**
	 * Extend the validator with new rules.
	 *
	 * @param  string $rule
	 * @return void
	 */
	protected function extendValidator($rule)
	{
		$method = studly_case($rule);
		$translation = $this->app['translator']->get('clamav-validator::validation');
		$this->app['validator']->extend($rule, 'Sunspikes\ClamavValidator\ClamavValidator@validate' . $method, $translation[$rule]);
		$this->app['validator']->replacer($rule, 'Sunspikes\ClamavValidator\ClamavValidator@replace' . $method);
	}


	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	}


	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}
}
