<?php

namespace Iperamuna\HtmlFragmentCache;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use DateInterval;
use DateTimeInterface;

class HtmlFragmentCache
{
    protected function getCacheStore(): CacheRepository
    {
        $storeName = config('fragment-cache.cache_store', 'default');
        return Cache::store($storeName);
    }

    public function key(string $identifier, string $variant = 'html_fragment', string $version = 'v1'): string
    {
        $id = Str::slug((string) $identifier, separator: ':');
        return "{$variant}:{$id}:{$version}";
    }

    public function rememberHtml(
        string $identifier,
        Closure $builder,
        DateTimeInterface|DateInterval|int|string|null $ttl = null,
        string $variant = 'html_fragment',
        string $version = 'v1'
    ): string {
        $cache = $this->getCacheStore();
        $key  = $this->key($identifier, $variant, $version);
        $ttl = $this->normalizeTtl($ttl);

        return $cache->remember($key, $ttl, function () use ($builder) {
            $html = $builder();
            if ($html instanceof Htmlable) {
                $html = $html->toHtml();
            }
            return (string) $html;
        });
    }

    public function forget(string $identifier, string $variant = 'html_fragment', string $version = 'v1'): void
    {
        $cache = $this->getCacheStore();
        $key = $this->key($identifier, $variant, $version);
        $cache->forget($key);
    }

    public function flushAll(): void
    {
        $cache = $this->getCacheStore();
        $cache->getStore()->flush();
    }

    public function getCacheStoreName(): string
    {
        return config('fragment-cache.cache_store', 'default');
    }

    public function getCacheStoreInfo(): array
    {
        $storeName = $this->getCacheStoreName();
        $cache = $this->getCacheStore();
        $store = $cache->getStore();

        return [
            'store_name' => $storeName,
            'store_class' => get_class($store),
            'driver' => 'unknown',
        ];
    }

    protected function normalizeTtl(DateTimeInterface|DateInterval|int|string|null $ttl)
    {
        if ($ttl instanceof DateTimeInterface || $ttl instanceof DateInterval || is_int($ttl)) {
            return $ttl;
        }

        $ttlString = $ttl ?: config('fragment-cache.default_ttl', '6 hours');
        try {
            return now()->add(\DateInterval::createFromDateString($ttlString));
        } catch (\Throwable $e) {
            return now()->addHours(6);
        }
    }
}
