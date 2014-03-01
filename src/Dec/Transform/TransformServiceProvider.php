<?php namespace Dec\Transform;

use Illuminate\Support\ServiceProvider;

class TransformServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{
	    $this->package('dec/transform');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('transformer', 'Dec\Transform\Transformer');
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
