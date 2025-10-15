<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Iperamuna\HtmlFragmentCache\HtmlFragmentCacheServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            HtmlFragmentCacheServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default cache configuration
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => false,
        ]);

        // Setup fragment cache configuration
        $app['config']->set('fragment-cache.cache_store', 'array');
        $app['config']->set('fragment-cache.default_ttl', '1 hour');
        $app['config']->set('fragment-cache.variant', 'test');
        $app['config']->set('fragment-cache.version', 'v1');
    }
}
