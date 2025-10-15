<?php

use Iperamuna\HtmlFragmentCache\HtmlFragmentCache;
use Iperamuna\HtmlFragmentCache\Facades\FragmentCache;
use Illuminate\Support\Facades\Cache;

it('stores and retrieves rendered html', function () {
    config(['cache.default' => 'array']);

    /** @var HtmlFragmentCache $svc */
    $svc = app('iperamuna.fragment-cache');

    $id = 'test:123';
    $variant = 'widget';
    $version = 'v1';

    $first = $svc->rememberHtml($id, fn () => '<p>Hello</p>', '10 minutes', $variant, $version);
    expect($first)->toBe('<p>Hello</p>');

    // mutate builder to ensure cache hit prevents rebuild
    $second = $svc->rememberHtml($id, fn () => '<p>Changed</p>', '10 minutes', $variant, $version);
    expect($second)->toBe('<p>Hello</p>');
});

it('forget removes cached entry', function () {
    config(['cache.default' => 'array']);

    $svc = app('iperamuna.fragment-cache');
    $id = 'test:to-forget';
    $variant = 'widget';
    $version = 'v1';

    $svc->rememberHtml($id, fn () => 'A', '10 minutes', $variant, $version);
    $svc->forget($id, $variant, $version);

    $fresh = $svc->rememberHtml($id, fn () => 'B', '10 minutes', $variant, $version);
    expect($fresh)->toBe('B');
});
