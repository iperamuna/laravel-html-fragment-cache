<?php

namespace Iperamuna\HtmlFragmentCache\Resolvers;

use Iperamuna\HtmlFragmentCache\Contracts\IdentifierResolver;

class DefaultIdentifierResolver implements IdentifierResolver
{
    public function resolve(object $context = null): ?string
    {
        $cfg = config('fragment-cache.identifier');

        // Try property-based sources on the context object, e.g., $this->customer->id
        foreach ((array) ($cfg['sources'] ?? []) as $source) {
            if (($source['type'] ?? null) === 'property' && $context) {
                $path = $source['path'] ?? null; // e.g., "customer.id"
                $label = $source['label'] ?? null;
                if ($path) {
                    $val = $this->readPropertyPath($context, $path);
                    if (!empty($val)) {
                        return $this->formatId($label, $val);
                    }
                }
            }
        }

        // Try route() sources from the request
        foreach ((array) ($cfg['sources'] ?? []) as $source) {
            if (($source['type'] ?? null) === 'route') {
                $name = $source['name'] ?? null; // e.g., "customer"
                $label = $source['label'] ?? null;
                if ($name) {
                    $param = request()->route($name);
                    if ($param) {
                        // If it's a model, use its key
                        if (is_object($param) && method_exists($param, 'getKey')) {
                            $param = $param->getKey();
                        }
                        if (!empty($param)) {
                            return $this->formatId($label, $param);
                        }
                    }
                }
            }
        }

        return null;
    }

    protected function readPropertyPath(object $obj, string $path): mixed
    {
        $parts = explode('.', $path);
        $val = $obj;
        foreach ($parts as $part) {
            if (is_object($val)) {
                if (isset($val->{$part})) {
                    $val = $val->{$part};
                } elseif (method_exists($val, '__get')) {
                    $val = $val->{$part};
                } else {
                    return null;
                }
            } elseif (is_array($val)) {
                $val = $val[$part] ?? null;
            } else {
                return null;
            }
        }
        // Unwrap model to key if possible
        if (is_object($val) && method_exists($val, 'getKey')) {
            $val = $val->getKey();
        }
        return $val;
    }

    protected function formatId(?string $label, $value): string
    {
        $prefix = (string) (config('fragment-cache.identifier.prefix') ?? '');
        $head = $label ? "{$label}:" : '';
        return "{$prefix}{$head}{$value}";
    }
}
