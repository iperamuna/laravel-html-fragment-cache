<?php

namespace Iperamuna\HtmlFragmentCache\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Iperamuna\HtmlFragmentCache\HtmlFragmentCache;
use Iperamuna\HtmlFragmentCache\Concerns\InteractsWithPrompts;

use function Laravel\Prompts\{select, text, confirm};

class ForgetFragmentsCommand extends Command
{
    use InteractsWithPrompts;

    protected $signature = 'fragment-cache:forget';

    protected $description = 'Forget cached HTML fragments using interactive prompts.';

    public function handle(): int
    {
        /** @var HtmlFragmentCache $svc */
        $svc = app('iperamuna.fragment-cache');

        $mode = select(
            label: 'Select operation mode',
            options: [
                'identifier' => 'Forget by Identifier',
                'pattern'    => 'Forget by Pattern',
                'all'        => 'Flush All (Dangerous)',
            ]
        );

        if ($mode === 'identifier') {
            $id = text('Enter identifier (e.g., customer:123)');
            $variant = text('Variant', default: 'default');
            $version = text('Version', default: '1.0');

            if (confirm("Forget cache for identifier={$id} (variant={$variant}, version={$version})?")) {
                $svc->forget($id, $variant, $version);
                $this->pInfo("Forgot identifier={$id}");
                return self::SUCCESS;
            }
            $this->pNote('Aborted.');
            return self::SUCCESS;
        }

        if ($mode === 'pattern') {
            $pattern = text('Enter key pattern (e.g., *customer:*)');
            $variant = text('Variant', default: 'default');
            $version = text('Version', default: '1.0');

            if (confirm("Delete keys matching pattern={$pattern} with variant={$variant} and version={$version}?")) {
                return $this->deleteByPattern($pattern, $variant, $version);
            }
            $this->pNote('Aborted.');
            return self::SUCCESS;
        }

        if ($mode === 'all') {
            if (confirm('⚠ This will flush ALL fragment-cache entries. Continue?')) {
                return $this->flushAll();
            }
            $this->pNote('Aborted.');
            return self::SUCCESS;
        }

        return self::SUCCESS;
    }

    protected function flushAll(): int
    {
        try {
            /** @var HtmlFragmentCache $svc */
            $svc = app('iperamuna.fragment-cache');
            $svc->flushAll();
            $storeName = $svc->getCacheStoreName();
            $this->pInfo("Flushed all fragments from cache store: {$storeName}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->pError('Unable to flush cache: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function deleteByPattern(string $pattern, string $variant, string $version): int
    {
        try {
            /** @var HtmlFragmentCache $svc */
            $svc = app('iperamuna.fragment-cache');
            $storeName = $svc->getCacheStoreName();

            // For pattern-based deletion, we need to flush the entire cache store
            // since most cache drivers don't support pattern-based key deletion
            $svc->flushAll();
            $this->pInfo("Flushed all fragments from cache store: {$storeName} (pattern-based deletion not supported by all cache drivers)");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->pError('Pattern deletion failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
