<?php


namespace Sdtech\BinanceApiLaravel\Providers;


use Illuminate\Support\ServiceProvider;
use Sdtech\BinanceApiLaravel\Service\BinanceApiLaravelService;

class BinanceApiLaravelServiceProviders extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @param
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/binanceapilaravel.php', 'binanceapilaravel'
        );
        $this->publishFiles();
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton("BinanceApiLaravelService",function ($app) {
            return new BinanceApiLaravelService();
        });
    }

    /**
     * Publish config file for the installer.
     *
     * @return void
     */
    protected function publishFiles()
    {
        $this->publishes([
            __DIR__ . '/../Config/binanceapilaravel.php' => config_path('binanceapilaravel.php'),
        ], 'binanceapilaravel');
    }

}
