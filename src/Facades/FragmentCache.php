<?php

namespace Iperamuna\HtmlFragmentCache\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string rememberHtml(string $identifier, \Closure $builder, $ttl = null, string $variant = 'html_fragment', string $version = 'v1')
 * @method static void forget(string $identifier, string $variant = 'html_fragment', string $version = 'v1')
 * @method static string key(string $identifier, string $variant = 'html_fragment', string $version = 'v1')
 * @method static bool supportsTags()
 */
class FragmentCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'iperamuna.fragment-cache';
    }
}
