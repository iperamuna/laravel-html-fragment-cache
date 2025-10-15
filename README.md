# Laravel HTML Fragment Cache

[![Latest Version on Packagist](https://img.shields.io/packagist/v/iperamuna/laravel-html-fragment-cache.svg?style=flat-square)](https://packagist.org/packages/iperamuna/laravel-html-fragment-cache)
[![Total Downloads](https://img.shields.io/packagist/dt/iperamuna/laravel-html-fragment-cache.svg?style=flat-square)](https://packagist.org/packages/iperamuna/laravel-html-fragment-cache)
[![License](https://img.shields.io/packagist/l/iperamuna/laravel-html-fragment-cache.svg?style=flat-square)](https://packagist.org/packages/iperamuna/laravel-html-fragment-cache)

A lightweight, **generic HTML fragment caching** package for Laravel. Cache any rendered HTML **by identifier** (customer/org/user/etc.) with support for all Laravel cache drivers, Blade directives, and simple invalidation.

## Features

- 🚀 **Laravel 10/11/12** compatible
- 💾 **Universal cache support** - Works with Redis, Memcached, Database, File, Array, DynamoDB, and Octane
- 🎯 **Identifier-based caching** - Cache by customer, organization, user, or any identifier
- 🏷️ **Blade directive** - Simple `@fragmentCache` directive for quick caching
- ⚡ **Performance optimized** - Skip database queries and Blade rendering on cache hits
- 🔧 **Configurable cache store** - Use any cache store from your Laravel configuration
- 🎨 **Clean API** - Simple facade and trait-based usage
- 🛠️ **Artisan commands** - Interactive commands for cache management

## Installation

You can install the package via Composer:

```bash
composer require iperamuna/laravel-html-fragment-cache
```

The package will automatically register its service provider and facade.

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Iperamuna\HtmlFragmentCache\HtmlFragmentCacheServiceProvider" --tag=config
```

This will publish `config/fragment-cache.php` where you can customize the default settings.

## Configuration

The package uses Laravel's cache system and supports all cache drivers. You can configure which cache store to use:

```bash
# In your .env file
FRAGMENT_CACHE_STORE=redis
# or
FRAGMENT_CACHE_STORE=memcached
# or
FRAGMENT_CACHE_STORE=database
# etc.
```

### Available Configuration Options

```php
// config/fragment-cache.php
return [
    // Cache store to use for fragment caching (must be one from config/cache.php stores)
    'cache_store' => env('FRAGMENT_CACHE_STORE', 'default'),
    
    // Default TTL for cached fragments
    'default_ttl' => env('FRAGMENT_CACHE_TTL', '6 hours'),
    
    // Default key parts
    'variant' => env('FRAGMENT_CACHE_VARIANT', 'html_fragment'),
    'version' => env('FRAGMENT_CACHE_VERSION', 'v1'),
    
    // Identifier resolution
    'identifier' => [
        'prefix' => env('FRAGMENT_CACHE_ID_PREFIX', ''),
        'sources' => [
            ['type' => 'property', 'path' => 'customer.id', 'label' => 'customer'],
            ['type' => 'property', 'path' => 'organization.id', 'label' => 'org'],
            ['type' => 'route', 'name' => 'customer', 'label' => 'customer'],
            ['type' => 'route', 'name' => 'organization', 'label' => 'org'],
        ],
        'resolver' => Iperamuna\HtmlFragmentCache\Resolvers\DefaultIdentifierResolver::class,
    ],
];
```

## Usage

### Basic Usage with Facade

```php
use FragmentCache;

public function show(Customer $customer)
{
    $html = FragmentCache::rememberHtml(
        identifier: "customer:{$customer->id}",
        builder: function () use ($customer) {
            $products = $customer->products()
                ->select('id','name','image_url','category')
                ->orderBy('name')
                ->get();

            return view('components.dashboard.products-widget', compact('products'))->render();
        },
        ttl: '6 hours',
        variant: 'products_widget_html',
        version: 'v1'
    );

    return view('dashboard.index', ['productsWidgetHtml' => $html]);
}
```

### Blade Directive

The package provides a convenient Blade directive for quick caching:

```blade
@fragmentCache('customer:'.$customer->id)
    {{-- expensive HTML here --}}
    <div class="products-widget">
        @foreach($customer->products as $product)
            <div class="product-item">{{ $product->name }}</div>
        @endforeach
    </div>
@endFragmentCache
```

You can also pass an **array** as identifier to include additional context:

```blade
@fragmentCache([$customer->id, app()->getLocale(), 'v2'])
    {{-- HTML with locale and version context --}}
@endFragmentCache
```

### Using the Trait

For more complex scenarios, use the `UsesHtmlFragmentCache` trait:

```php
use Iperamuna\HtmlFragmentCache\Concerns\UsesHtmlFragmentCache;

class ProductController extends Controller
{
    use UsesHtmlFragmentCache;

    public function show(Customer $customer)
    {
        $html = $this->cacheFragmentHtml(
            identifier: "customer:{$customer->id}",
            builder: function () use ($customer) {
                return view('components.products-widget', [
                    'products' => $customer->products()->orderBy('name')->get()
                ])->render();
            },
            variant: 'products_widget',
            version: 'v1'
        );

        return view('dashboard.index', ['productsWidgetHtml' => $html]);
    }
}
```

## Livewire Integration

Perfect for caching expensive Livewire component rendering:

### Using the Trait (Recommended)

```php
<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use Illuminate\Support\HtmlString;
use Iperamuna\HtmlFragmentCache\Concerns\Livewire\CachesRenderedHtml;
use Livewire\Component;
use Livewire\Livewire;

class ActiveProducts extends Component
{
    use CachesRenderedHtml;

    public function render()
    {
        // This will short-circuit on cache hit. The closure only runs on a cache MISS.
        return $this->renderCached(function () {
            // Query (only on cache miss)
            $products = Product::all();

            // Render each row ONCE into HTML (only when building cache)
            $items = $products->map(
                fn ($p) => Livewire::mount('dashboard.active-product', ['product' => $p])
            )->implode('');

            // Render your dashboard-section wrapper and inject the items into the slot
            return view('components.dashboard-section', [
                'title' => 'Active Products',
                'url'   => url('/dashboard/products'),
            ])->with('slot', new HtmlString($items));
        },
            // optional overrides (you can omit these to fully rely on config defaults)
            identifier: 'organization:'. auth()->user()->current_organization_id,                       // null → resolve via config (customer/org)
            variant: 'dashboard_active_products',   // bucket
            version: 'v1',                          // bump on markup change
            ttl: '6 hours'                          // or int/DateInterval
        );
    }
}
```

### Using the Facade

```php
<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use FragmentCache;

class ActiveProducts extends Component
{
    public string $identifier; // e.g., 'customer:123'

    public function mount(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function render()
    {
        $html = FragmentCache::rememberHtml(
            identifier: $this->identifier,
            builder: function () {
                $products = \App\Models\Product::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                $items = $products->map(function ($product) {
                    return \Livewire\Livewire::mount('dashboard.active-product', ['product' => $product])->html();
                })->implode('');

                return view('livewire.dashboard.active-products', ['itemsHtml' => $items])->render();
            },
            ttl: '6 hours',
            variant: 'active_products_widget',
            version: 'v1'
        );

        return view('livewire.dashboard.active-products-cached', ['html' => $html]);
    }
}
```

## Cache Invalidation

### Forget Specific Fragments

```php
// Forget by identifier
FragmentCache::forget("customer:{$customerId}", variant: 'products_widget_html');

// Forget with version
FragmentCache::forget("customer:{$customerId}", variant: 'products_widget_html', version: 'v1');
```

### Artisan Commands

The package provides interactive Artisan commands for cache management:

```bash
# Interactive cache management
php artisan fragment-cache:forget
```

The `fragment-cache:forget` command provides three modes:
- **Forget by Identifier**: Remove specific cached fragments
- **Forget by Pattern**: Clear fragments matching a pattern (flushes entire cache store)
- **Flush All**: Clear all fragments from the configured cache store

## Advanced Usage

### Custom Identifier Resolution

You can customize how identifiers are resolved by modifying the configuration or creating a custom resolver:

```php
// Custom resolver
class CustomIdentifierResolver implements IdentifierResolver
{
    public function resolve(?object $context = null): ?string
    {
        // Your custom logic here
        return 'custom:identifier';
    }
}
```

### Multiple Cache Stores

You can use different cache stores for different environments:

```bash
# Development
FRAGMENT_CACHE_STORE=array

# Staging
FRAGMENT_CACHE_STORE=file

# Production
FRAGMENT_CACHE_STORE=redis
```

## Performance Benefits

- **Skip Database Queries**: Cache the final rendered HTML, not just database results
- **Skip Blade Rendering**: Avoid expensive view compilation on cache hits
- **Universal Compatibility**: Works with any Laravel cache driver
- **Memory Efficient**: Uses Laravel's built-in cache system

## Testing

```bash
composer test
```

The test suite uses Orchestra Testbench with the array cache driver for portability.

## Requirements

- PHP 8.2+
- Laravel 10.0+ | 11.0+ | 12.0+
- Laravel Prompts (for interactive commands)

## Cache Driver Support

| Driver | Support | Notes |
|--------|---------|-------|
| Array | ✅ Full | Perfect for testing |
| File | ✅ Full | Good for single-server setups |
| Database | ✅ Full | Persistent across servers |
| Redis | ✅ Full | High performance, recommended for production |
| Memcached | ✅ Full | High performance, in-memory |
| DynamoDB | ✅ Full | AWS managed cache |
| Octane | ✅ Full | Ultra-high performance |

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email iperamuna@gmail.com instead of using the issue tracker.

## Credits

- [Indunil Peramuna](https://github.com/iperamuna)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

---

**Made with ❤️ for the Laravel community**
