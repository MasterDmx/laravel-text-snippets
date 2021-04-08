<?php

namespace MasterDmx\LaravelTextSnippets;

use Illuminate\Support\ServiceProvider;

class TextSnippetsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/text_snippets.php' => config_path('text_snippets.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/../config/text_snippets.php', 'text_snippets');

        $this->app->singleton(TextSnippetsManager::class);
    }
}
