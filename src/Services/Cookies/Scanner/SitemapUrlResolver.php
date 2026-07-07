<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class SitemapUrlResolver
{
    public function resolve(string $sitemapUrl, ?int $limit = null): array
    {
        $response = Http::timeout(20)->get($sitemapUrl);

        $response->throw();

        $xml = new SimpleXMLElement($response->body());

        $urls = [];

        if (isset($xml->sitemap)) {
            foreach ($xml->sitemap as $sitemap) {
                $loc = trim((string) $sitemap->loc);

                if ($loc === '') {
                    continue;
                }

                $urls = [
                    ...$urls,
                    ...$this->resolve($loc, $this->remainingLimit($limit, $urls)),
                ];

                if ($limit !== null && count($urls) >= $limit) {
                    break;
                }
            }

            return array_slice(array_values(array_unique($urls)), 0, $limit);
        }

        foreach ($xml->url as $url) {
            $loc = trim((string) $url->loc);

            if ($loc !== '') {
                $urls[] = $loc;
            }

            if ($limit !== null && count($urls) >= $limit) {
                break;
            }
        }

        return array_slice(array_values(array_unique($urls)), 0, $limit);
    }

    protected function remainingLimit(?int $limit, array $urls): ?int
    {
        if ($limit === null) {
            return null;
        }

        return max(0, $limit - count($urls));
    }
}
