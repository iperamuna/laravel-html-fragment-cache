<?php

namespace Iperamuna\HtmlFragmentCache\Concerns;

use function Laravel\Prompts\{info, error, note, table};


trait InteractsWithPrompts
{
    protected function pInfo(string $message): void
    {
        if (function_exists('Laravel\Prompts\info')) {
            info($message);
        } else {
            $this->info($message);
        }
    }

    protected function pError(string $message): void
    {
        if (function_exists('Laravel\Prompts\error')) {
            error($message);
        } else {
            $this->error($message);
        }
    }

    protected function pNote(string $message): void
    {
        if (function_exists('Laravel\Prompts\note')) {
            note($message);
        } else {
            $this->line($message);
        }
    }

    protected function pTable(array $headers, array $rows): void
    {
        if (function_exists('Laravel\Prompts\table')) {
            table($headers, $rows);
        } else {
            $this->table($headers, $rows);
        }
    }
}
