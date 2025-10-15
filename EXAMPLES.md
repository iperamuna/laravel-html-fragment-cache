# Usage Examples

This document provides practical examples of how to use Laravel HTML Fragment Cache in various scenarios.

## Basic Controller Usage

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use FragmentCache;

class ProductController extends Controller
{
    public function index()
    {
        $html = FragmentCache::rememberHtml(
            identifier: 'products:index',
            builder: function () {
                $products = \App\Models\Product::with('category')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                return view('products.index', compact('products'))->render();
            },
            ttl: '2 hours',
            variant: 'products_list',
            version: 'v1'
        );

        return view('dashboard', ['productsHtml' => $html]);
    }
}
```

## User-Specific Caching

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use FragmentCache;

class DashboardController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        
        $html = FragmentCache::rememberHtml(
            identifier: "user:{$user->id}:dashboard",
            builder: function () use ($user) {
                $recentOrders = $user->orders()
                    ->with('items')
                    ->latest()
                    ->limit(5)
                    ->get();

                $recommendations = $this->getRecommendations($user);

                return view('dashboard.widgets', [
                    'recentOrders' => $recentOrders,
                    'recommendations' => $recommendations
                ])->render();
            },
            ttl: '1 hour',
            variant: 'user_dashboard',
            version: 'v2'
        );

        return view('dashboard.index', ['widgetsHtml' => $html]);
    }
}
```

## Blade Directive Examples

### Simple Caching

```blade
@fragmentCache('products:featured')
    <div class="featured-products">
        @foreach(\App\Models\Product::featured()->take(6)->get() as $product)
            <div class="product-card">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                <h3>{{ $product->name }}</h3>
                <p class="price">${{ $product->price }}</p>
            </div>
        @endforeach
    </div>
@endFragmentCache
```

### Multi-Context Caching

```blade
@fragmentCache([$category->id, app()->getLocale(), 'v3'])
    <div class="category-products">
        <h2>{{ $category->name }}</h2>
        @foreach($category->products()->active()->get() as $product)
            <div class="product-item">
                <h3>{{ $product->name }}</h3>
                <p>{{ $product->description }}</p>
            </div>
        @endforeach
    </div>
@endFragmentCache
```

## Livewire Component Caching

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use FragmentCache;

class ProductList extends Component
{
    public $categoryId;
    public $search = '';

    public function mount($categoryId = null)
    {
        $this->categoryId = $categoryId;
    }

    public function render()
    {
        $cacheKey = "products:category:{$this->categoryId}:search:" . md5($this->search);
        
        $html = FragmentCache::rememberHtml(
            identifier: $cacheKey,
            builder: function () {
                $query = \App\Models\Product::query();
                
                if ($this->categoryId) {
                    $query->where('category_id', $this->categoryId);
                }
                
                if ($this->search) {
                    $query->where('name', 'like', "%{$this->search}%");
                }
                
                $products = $query->with('category')->paginate(12);

                return view('livewire.product-list', compact('products'))->render();
            },
            ttl: '30 minutes',
            variant: 'product_list',
            version: 'v1'
        );

        return view('livewire.product-list-cached', ['html' => $html]);
    }
}
```

## API Response Caching

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use FragmentCache;

class ProductApiController extends Controller
{
    public function index()
    {
        $html = FragmentCache::rememberHtml(
            identifier: 'api:products:index',
            builder: function () {
                $products = \App\Models\Product::with(['category', 'images'])
                    ->where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->get();

                return view('api.products.index', compact('products'))->render();
            },
            ttl: '1 hour',
            variant: 'api_response',
            version: 'v1'
        );

        return response()->json([
            'html' => $html,
            'cached_at' => now()->toISOString()
        ]);
    }
}
```

## Cache Invalidation Examples

### Manual Invalidation

```php
<?php

namespace App\Services;

use FragmentCache;

class ProductService
{
    public function updateProduct($productId, $data)
    {
        $product = \App\Models\Product::findOrFail($productId);
        $product->update($data);

        // Invalidate related caches
        FragmentCache::forget("products:category:{$product->category_id}", variant: 'product_list');
        FragmentCache::forget('products:featured', variant: 'featured_products');
        FragmentCache::forget("product:{$productId}", variant: 'product_detail');

        return $product;
    }
}
```

### Event-Based Invalidation

```php
<?php

namespace App\Listeners;

use FragmentCache;
use App\Events\ProductUpdated;

class InvalidateProductCache
{
    public function handle(ProductUpdated $event)
    {
        $product = $event->product;
        
        // Invalidate category-specific caches
        FragmentCache::forget("products:category:{$product->category_id}", variant: 'product_list');
        
        // Invalidate featured products cache if this product is featured
        if ($product->is_featured) {
            FragmentCache::forget('products:featured', variant: 'featured_products');
        }
        
        // Invalidate user-specific caches
        $users = $product->favorites()->pluck('user_id');
        foreach ($users as $userId) {
            FragmentCache::forget("user:{$userId}:favorites", variant: 'user_favorites');
        }
    }
}
```

## Configuration Examples

### Environment-Specific Configuration

```bash
# .env.local (development)
FRAGMENT_CACHE_STORE=array
FRAGMENT_CACHE_TTL=5 minutes

# .env.staging
FRAGMENT_CACHE_STORE=file
FRAGMENT_CACHE_TTL=1 hour

# .env.production
FRAGMENT_CACHE_STORE=redis
FRAGMENT_CACHE_TTL=6 hours
```

### Custom Configuration

```php
// config/fragment-cache.php
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
        'resolver' => \App\Resolvers\CustomIdentifierResolver::class,
    ],
];
```

## Performance Tips

1. **Use appropriate TTL values**: Don't cache too long for dynamic content
2. **Choose the right cache store**: Redis for production, Array for testing
3. **Cache at the right level**: Cache expensive operations, not simple queries
4. **Invalidate strategically**: Only invalidate what's actually affected
5. **Monitor cache hit rates**: Use cache inspection commands to monitor performance

## Common Patterns

### Conditional Caching

```php
$html = FragmentCache::rememberHtml(
    identifier: "products:category:{$categoryId}",
    builder: function () use ($categoryId) {
        return view('products.category', [
            'products' => \App\Models\Product::where('category_id', $categoryId)->get()
        ])->render();
    },
    ttl: $categoryId === 1 ? '1 hour' : '30 minutes', // Different TTL for different categories
    variant: 'product_list',
    version: 'v1'
);
```

### Fallback Caching

```php
try {
    $html = FragmentCache::rememberHtml(
        identifier: "expensive:operation:{$id}",
        builder: function () use ($id) {
            return $this->expensiveOperation($id);
        },
        ttl: '1 hour'
    );
} catch (\Exception $e) {
    // Fallback to non-cached version
    $html = $this->expensiveOperation($id);
}
```
