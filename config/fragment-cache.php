<?php

return [

    // Enable or disable fragment caching globally
    'enabled' => env('FRAGMENT_CACHE_ENABLED', true),

    // Cache store to use for fragment caching (must be one from config/cache.php stores)
    'cache_store' => env('FRAGMENT_CACHE_STORE', 'default'),

    // Default TTL for cached fragments (string|int|DateInterval|DateTimeInterface)
    'default_ttl' => env('FRAGMENT_CACHE_TTL', '6 hours'),

    // Default key parts
    'variant' => env('FRAGMENT_CACHE_VARIANT', 'html_fragment'),
    'version' => env('FRAGMENT_CACHE_VERSION', 'v1'),

    // Identifier resolution
    'identifier' => [
        // Optional prefix added to the resolved ID, e.g. 'customer:' → 'customer:123'
        'prefix' => env('FRAGMENT_CACHE_ID_PREFIX', ''),

        // Try these sources in order until one resolves to a non-empty value.
        // You can remove or reorder entries to suit your app.
        // type=property: reads $this->customer->id or $this->organization->id if present
        // type=route:    reads request()->route('customer') or route model binding id
        'sources' => [
            ['type' => 'property', 'path' => 'customer.id', 'label' => 'customer'],
            ['type' => 'property', 'path' => 'organization.id', 'label' => 'org'],
            ['type' => 'route',    'name' => 'customer',       'label' => 'customer'],
            ['type' => 'route',    'name' => 'organization',   'label' => 'org'],
        ],

        // Class that implements IdentifierResolver::resolve(object $context = null): ?string
        'resolver' => Iperamuna\HtmlFragmentCache\Resolvers\DefaultIdentifierResolver::class,
    ],
];
