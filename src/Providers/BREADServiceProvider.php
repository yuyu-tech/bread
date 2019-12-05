<?php

namespace Yuyu\BREAD\Providers;

use Illuminate\Support\ServiceProvider;

class BREADServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(){
        // Register resources and console commands if app is running in console.
        if ($this->app->runningInConsole()) {
            $this->registerPublishableResources();
            $this->registerConsoleCommands();
        }

        // Register BREAD Service
        $this->app->singleton('bread', function($app){
            // dd($app);
            return new \Yuyu\BREAD\Controllers\BREADController;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        // Console Commands
    }

    /**
     * Register the publishable files.
     */
    private function registerPublishableResources()
    {
        $publishablePath = __DIR__.'/../../publishable';

        $publishable = [
            // 'migration' => [
            //     "{$publishablePath}/database/migrations/" => database_path('migrations'),
            // ],
            // 'seeds' => [
            //     "{$publishablePath}/database/seeds/" => database_path('seeds'),
            // ],
            // 'models' => [
            //     "{$publishablePath}/Models" => app_path('Models'),
            // ]
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }
}
