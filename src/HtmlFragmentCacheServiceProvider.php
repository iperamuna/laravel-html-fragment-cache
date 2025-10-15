<?php

namespace Iperamuna\HtmlFragmentCache;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Iperamuna\HtmlFragmentCache\HtmlFragmentCache;

class HtmlFragmentCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/fragment-cache.php', 'fragment-cache');

        $this->app->singleton('iperamuna.fragment-cache', function () {
            return new HtmlFragmentCache();
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'fragment-cache');


        if ($this->app->runningInConsole()) {
            $this->commands([
                \Iperamuna\HtmlFragmentCache\Console\ForgetFragmentsCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/fragment-cache.php' => config_path('fragment-cache.php'),
        ], 'config');

        /*
         * Blade directive:
         * @fragmentCache('identifier', 'variant', '6 hours', 'v1')
         *   ... expensive markup ...
         * @endFragmentCache
         *
         * For flexibility, we accept either a plain string identifier or an array that we implode into a key.
         */
        Blade::directive('fragmentCache', function ($expression) {
            return "<?php "
                . " $__frag_args = {$expression}; "
                . " if (is_array($__frag_args)) { "
                . "     \$__frag_identifier = implode('|', $__frag_args); "
                . " } else { "
                . "     \$__frag_identifier = $__frag_args; "
                . " } "
                . " ob_start(); ?>";
        });

        Blade::directive('endFragmentCache', function () {
            return "<?php "
                . " \$__frag_html_raw = ob_get_clean(); "
                . " echo app('iperamuna.fragment-cache')->rememberHtml(\$__frag_identifier, function() use (\$__frag_html_raw) { return \$__frag_html_raw; }); "
                . " unset(\$__frag_args, \$__frag_identifier, \$__frag_html_raw); ?>";
        });
    }
}
