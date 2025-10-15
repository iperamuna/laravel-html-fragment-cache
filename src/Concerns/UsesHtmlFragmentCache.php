<?php

namespace Iperamuna\HtmlFragmentCache\Concerns;

use Iperamuna\HtmlFragmentCache\HtmlFragmentCache;
use Iperamuna\HtmlFragmentCache\Contracts\IdentifierResolver;

trait UsesHtmlFragmentCache
{
    protected function fragmentCache(): HtmlFragmentCache
    {
        return app('iperamuna.fragment-cache');
    }

    protected function resolveFragmentIdentifier(?array $extra = null): ?string
    {
        /** @var IdentifierResolver $resolver */
        $resolver = app(config('fragment-cache.identifier.resolver'));
        $id = $resolver->resolve($this);
        if (!$id) return null;

        if ($extra && count($extra)) {
            // Append extra parts for uniqueness
            $id .= ':' . implode(':', array_map(fn($v) => is_scalar($v) ? $v : md5(json_encode($v)), $extra));
        }
        return $id;
    }

    protected function cacheFragmentHtml(
        ?string $identifier,
        callable $builder,
        ?string $variant = null,
        ?string $version = null,
        $ttl = null
    ): string {
        $variant = $variant ?? config('fragment-cache.variant', 'html_fragment');
        $version = $version ?? config('fragment-cache.version', 'v1');
        $ttl     = $ttl     ?? config('fragment-cache.default_ttl', '6 hours');

        $identifier = $identifier ?? $this->resolveFragmentIdentifier();

        if (!$identifier) {
            // No identifier → just build once (no caching)
            $html = $builder();
            return $html instanceof \Illuminate\Contracts\Support\Htmlable ? $html->toHtml() : (string) $html;
        }

        return $this->fragmentCache()->rememberHtml(
            identifier: $identifier,
            builder: function () use ($builder) {
                $html = $builder();
                return $html instanceof \Illuminate\Contracts\Support\Htmlable ? $html->toHtml() : (string) $html;
            },
            ttl: $ttl,
            variant: $variant,
            version: $version
        );
    }
}
