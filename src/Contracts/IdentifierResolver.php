<?php

namespace Iperamuna\HtmlFragmentCache\Contracts;

interface IdentifierResolver
{
    /**
     * Resolve a cache identifier string from a given context (e.g., a Livewire or Blade component).
     * Return null if you cannot resolve.
     */
    public function resolve(object $context = null): ?string;
}
