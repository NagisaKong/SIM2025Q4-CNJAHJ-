<?php

namespace App\Core;

class View
{
    public function __construct(private string $basePath)
    {
    }

    public function render(string $template, array $data = []): string
    {
        $path = rtrim($this->basePath, '/').'/'.ltrim($template, '/');
        if (!file_exists($path)) {
            throw new \RuntimeException("View '{$template}' not found at '{$path}'.");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return ob_get_clean() ?: '';
    }
}
