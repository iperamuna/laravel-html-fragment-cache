# Installation Guide

This guide will help you install and configure Laravel HTML Fragment Cache in your Laravel application.

## Prerequisites

- PHP 8.2 or higher
- Laravel 10.0, 11.0, or 12.0
- Composer

## Installation

### Step 1: Install the Package

Install the package via Composer:

```bash
composer require iperamuna/laravel-html-fragment-cache
```

### Step 2: Publish Configuration (Optional)

Publish the configuration file to customize the package settings:

```bash
php artisan vendor:publish --provider="Iperamuna\HtmlFragmentCache\HtmlFragmentCacheServiceProvider" --tag=config
```

This will create `config/fragment-cache.php` in your application.

### Step 3: Configure Cache Store

The package uses Laravel's cache system. You can specify which cache store to use by setting the `FRAGMENT_CACHE_STORE` environment variable in your `.env` file:

```bash
# Use Redis (recommended for production)
FRAGMENT_CACHE_STORE=redis

# Use Memcached
FRAGMENT_CACHE_STORE=memcached

# Use Database
FRAGMENT_CACHE_STORE=database

# Use File (good for single-server setups)
FRAGMENT_CACHE_STORE=file

# Use Array (good for testing)
FRAGMENT_CACHE_STORE=array
```

### Step 4: Configure Cache Driver

Make sure your chosen cache store is properly configured in `config/cache.php`. For example, if using Redis:

```php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
        'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
    ],
],
```

And set the default cache driver:

```bash
CACHE_STORE=redis
```

## Quick Start

### Basic Usage

```php
use FragmentCache;

// In your controller
public function index()
{
    $html = FragmentCache::rememberHtml(
        identifier: 'products:index',
        builder: function () {
            $products = \App\Models\Product::all();
            return view('products.index', compact('products'))->render();
        },
        ttl: '1 hour'
    );

    return view('dashboard', ['productsHtml' => $html]);
}
```

### Blade Directive

```blade
@fragmentCache('products:featured')
    <div class="featured-products">
        @foreach(\App\Models\Product::featured()->get() as $product)
            <div class="product">{{ $product->name }}</div>
        @endforeach
    </div>
@endFragmentCache
```

## Configuration Options

### Environment Variables

```bash
# Cache store to use
FRAGMENT_CACHE_STORE=redis

# Default TTL for cached fragments
FRAGMENT_CACHE_TTL=6 hours

# Default variant for cache keys
FRAGMENT_CACHE_VARIANT=html_fragment

# Default version for cache keys
FRAGMENT_CACHE_VERSION=v1

# Optional prefix for identifiers
FRAGMENT_CACHE_ID_PREFIX=
```

### Configuration File

If you published the config file, you can customize these settings in `config/fragment-cache.php`:

```php
return [
    'cache_store' => env('FRAGMENT_CACHE_STORE', 'default'),
    'default_ttl' => env('FRAGMENT_CACHE_TTL', '6 hours'),
    'variant' => env('FRAGMENT_CACHE_VARIANT', 'html_fragment'),
    'version' => env('FRAGMENT_CACHE_VERSION', 'v1'),
    
    'identifier' => [
        'prefix' => env('FRAGMENT_CACHE_ID_PREFIX', ''),
        'sources' => [
            ['type' => 'property', 'path' => 'user.id', 'label' => 'user'],
            ['type' => 'property', 'path' => 'organization.id', 'label' => 'org'],
            ['type' => 'route', 'name' => 'user', 'label' => 'user'],
            ['type' => 'route', 'name' => 'organization', 'label' => 'org'],
        ],
        'resolver' => Iperamuna\HtmlFragmentCache\Resolvers\DefaultIdentifierResolver::class,
    ],
];
```

## Cache Store Setup

### Redis Setup

1. Install Redis server
2. Configure Redis in `config/database.php`:

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

3. Set environment variables:

```bash
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

### Memcached Setup

1. Install Memcached server
2. Install PHP Memcached extension
3. Configure Memcached in `config/cache.php`:

```php
'memcached' => [
    'driver' => 'memcached',
    'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
    'sasl' => [
        env('MEMCACHED_USERNAME'),
        env('MEMCACHED_PASSWORD'),
    ],
    'options' => [
        // Memcached::OPT_CONNECT_TIMEOUT => 2000,
    ],
    'servers' => [
        [
            'host' => env('MEMCACHED_HOST', '127.0.0.1'),
            'port' => env('MEMCACHED_PORT', 11211),
            'weight' => 100,
        ],
    ],
],
```

4. Set environment variables:

```bash
MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211
MEMCACHED_USERNAME=null
MEMCACHED_PASSWORD=null
```

### Database Cache Setup

1. Create cache table migration:

```bash
php artisan cache:table
php artisan migrate
```

2. Set environment variables:

```bash
DB_CACHE_CONNECTION=mysql
DB_CACHE_TABLE=cache
```

## Testing the Installation

### Test Basic Functionality

Create a simple test route:

```php
// routes/web.php
Route::get('/test-cache', function () {
    $html = FragmentCache::rememberHtml(
        identifier: 'test:cache',
        builder: function () {
            return '<div>This is cached content: ' . now() . '</div>';
        },
        ttl: '1 minute'
    );
    
    return $html;
});
```

Visit `/test-cache` and refresh the page. The timestamp should remain the same for 1 minute.

### Test Cache Commands

```bash
# Inspect cache store
php artisan fragment-cache:inspect-store

# Interactive cache management
php artisan fragment-cache:forget
```

## Troubleshooting

### Common Issues

1. **Cache not working**: Check that your cache store is properly configured and running
2. **Permission errors**: Ensure your application has write permissions to cache directories
3. **Memory issues**: Monitor memory usage when caching large HTML fragments

### Debug Mode

Enable debug mode to see cache operations:

```bash
# In .env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Cache Inspection

Use the provided Artisan commands to inspect your cache setup:

```bash
php artisan fragment-cache:inspect-store
```

This will show you:
- Which cache store is being used
- Cache store configuration
- Driver information
- Connection status

## Next Steps

- Read the [Usage Examples](EXAMPLES.md) for more advanced usage patterns
- Check the [README.md](README.md) for complete documentation
- Explore the [API documentation](README.md#api-reference) for all available methods

## Support

If you encounter any issues during installation:

1. Check the [troubleshooting section](#troubleshooting)
2. Review the [GitHub issues](https://github.com/iperamuna/laravel-html-fragment-cache/issues)
3. Create a new issue with detailed information about your setup
