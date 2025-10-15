<?php

namespace Iperamuna\HtmlFragmentCache\Concerns;

trait CachesBladeComponentHtml
{
    use UsesHtmlFragmentCache;

    /**
     * In your Blade component class:
     * public function render() {
     *   return $this->renderCachedComponent(fn () => view('...'));
     * }
     */
    protected function renderCachedComponent(callable $buildView, ?string $identifier = null, ?string $variant = null, ?string $version = null, $ttl = null)
    {
        $html = $this->cacheFragmentHtml(
            identifier: $identifier ?? $this->resolveFragmentIdentifier(),
            builder: function () use ($buildView) {
                $view = $buildView();
                return $view->render();
            },
            variant: $variant,
            version: $version,
            ttl: $ttl
        );

        return view('fragment-cache::render', ['html' => $html]);
    }
}
