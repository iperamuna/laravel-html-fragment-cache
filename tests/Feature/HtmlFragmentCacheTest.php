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

it('bypasses cache when disabled', function () {
    config(['cache.default' => 'array']);
    config(['fragment-cache.enabled' => false]);

    /** @var HtmlFragmentCache $svc */
    $svc = app('iperamuna.fragment-cache');

    $id = 'test:disabled';
    $variant = 'widget';
    $version = 'v1';

    // First call should execute builder
    $first = $svc->rememberHtml($id, fn () => '<p>First</p>', '10 minutes', $variant, $version);
    expect($first)->toBe('<p>First</p>');

    // Second call should also execute builder (no caching)
    $second = $svc->rememberHtml($id, fn () => '<p>Second</p>', '10 minutes', $variant, $version);
    expect($second)->toBe('<p>Second</p>');
});

it('forget does nothing when disabled', function () {
    config(['cache.default' => 'array']);
    config(['fragment-cache.enabled' => false]);

    $svc = app('iperamuna.fragment-cache');
    $id = 'test:disabled-forget';
    $variant = 'widget';
    $version = 'v1';

    // This should not throw any errors
    $svc->forget($id, $variant, $version);

    expect(true)->toBeTrue(); // Just ensure no exceptions were thrown
});

it('isEnabled returns correct status', function () {
    config(['fragment-cache.enabled' => true]);
    $svc = app('iperamuna.fragment-cache');
    expect($svc->isEnabled())->toBeTrue();

    config(['fragment-cache.enabled' => false]);
    $svc = app('iperamuna.fragment-cache');
    expect($svc->isEnabled())->toBeFalse();

    // Test default value - set to null to test default behavior
    config(['fragment-cache.enabled' => null]);
    $svc = app('iperamuna.fragment-cache');
    expect($svc->isEnabled())->toBeFalse(); // null should be cast to false
});
