<?php

namespace KostantinoAbate\Complihance\Services\Rendering;

class ComplihanceScriptRenderer
{
    public function render(): string
    {
        return view('complihance::partials.script', [
            'assets' => $this->assets(),
        ])->render();
    }

    protected function assets(): string
    {
        $devServer = config('complihance.vite.dev_server');
        $manifestPath = public_path('vendor/complihance/.vite/manifest.json');

        if (app()->environment('local') && $devServer) {
            return '<script type="module" src="'.e(rtrim($devServer, '/').'/resources/js/complihance.js').'"></script>';
        }

        if (! file_exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (! is_array($manifest)) {
            return '';
        }

        $entry = $manifest['resources/js/complihance.js'] ?? null;

        if (! $entry) {
            return '';
        }

        $html = '';

        foreach (($entry['css'] ?? []) as $css) {
            $html .= '<link rel="stylesheet" href="'.e(asset('vendor/complihance/'.$css)).'">';
        }

        $html .= '<script type="module" src="'.e(asset('vendor/complihance/'.$entry['file'])).'"></script>';

        return $html;
    }
}
