<?php

use Orchestra\Testbench\TestCase as Orchestra;
use Iperamuna\HtmlFragmentCache\HtmlFragmentCacheServiceProvider;

uses(Orchestra::class)->in('Feature', 'Unit');

/**
 * Setup Testbench
 */
function getPackageProviders($app)
{
    return [HtmlFragmentCacheServiceProvider::class];
}
