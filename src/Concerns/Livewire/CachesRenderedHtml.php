<?php

namespace Iperamuna\HtmlFragmentCache\Concerns\Livewire;

use Iperamuna\HtmlFragmentCache\Concerns\UsesHtmlFragmentCache;

trait CachesRenderedHtml
{
    use UsesHtmlFragmentCache;

    /**
     * In your Livewire component's render():
     *
     * return $this->renderCached(fn () => view('...'));
     */
    protected function renderCached(callable $buildView, ?string $identifier = null, ?string $variant = null, ?string $version = null, $ttl = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        $html = $this->cacheFragmentHtml(
            identifier: $identifier ?? $this->resolveFragmentIdentifier(),
            builder: function () use ($buildView) {
                $view = $buildView();
                // Mounting children here is fine; we render to string once.
                return $view->render();
            },
            variant: $variant,
            version: $version,
            ttl: $ttl
        );

        return view('fragment-cache::render', ['html' => $html]);
    }
}
