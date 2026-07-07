<?php

namespace KostantinoAbate\Complihance\Services\Rendering;

use JsonException;
use Throwable;

class ComplihanceScriptRenderer
{
    /**
     * Render the package script partial with the resolved frontend assets.
     *
     * @throws Throwable
     */
    public function render(): string
    {
        return view('complihance::partials.script', [
            'assets' => $this->assets(),
        ])->render();
    }

    /**
     * Resolve the HTML tags required to load the package frontend assets.
     */
    protected function assets(): string
    {
        $devServer = config('complihance.vite.dev_server');
        $manifestPath = public_path('vendor/complihance/.vite/manifest.json');

        if (app()->environment('local') && is_string($devServer) && $devServer !== '') {
            return '<script type="module" src="'.e(rtrim($devServer, '/').'/resources/js/complihance.js').'"></script>';
        }

        $manifest = $this->readManifest($manifestPath);
        $entry = $manifest['resources/js/complihance.js'] ?? null;

        if (! is_array($entry) || ! isset($entry['file'])) {
            return '';
        }

        $html = '';

        foreach (($entry['css'] ?? []) as $css) {
            if (is_string($css) && $css !== '') {
                $html .= '<link rel="stylesheet" href="'.e(asset('vendor/complihance/'.$css)).'">';
            }
        }

        if (! is_string($entry['file']) || $entry['file'] === '') {
            return $html;
        }

        return $html.'<script type="module" src="'.e(asset('vendor/complihance/'.$entry['file'])).'"></script>';
    }

    /**
     * Read and decode the published Vite manifest.
     *
     * @return array<string, mixed>
     */
    protected function readManifest(string $path): array
    {
        if (! file_exists($path)) {
            return [];
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
