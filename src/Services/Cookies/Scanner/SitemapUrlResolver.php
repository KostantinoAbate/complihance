<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class SitemapUrlResolver
{
    /**
     * Resolve all URLs declared in a sitemap or sitemap index.
     *
     * @return array<int, string>
     */
    public function resolve(string $sitemapUrl, ?int $limit = null): array
    {
        if ($limit !== null && $limit <= 0) {
            return [];
        }

        $response = Http::timeout(20)->get($sitemapUrl);
        $response->throw();

        $xml = new SimpleXMLElement($response->body());

        $urls = $this->isSitemapIndex($xml)
            ? $this->resolveSitemapIndex($xml, $limit)
            : $this->resolveUrlSet($xml, $limit);

        return array_slice(array_values(array_unique($urls)), 0, $limit);
    }

    /**
     * Determine whether the XML document is a sitemap index.
     */
    protected function isSitemapIndex(SimpleXMLElement $xml): bool
    {
        return isset($xml->sitemap);
    }

    /**
     * Resolve URLs from a sitemap index by recursively reading child sitemaps.
     *
     * @return array<int, string>
     */
    protected function resolveSitemapIndex(SimpleXMLElement $xml, ?int $limit = null): array
    {
        $urls = [];

        foreach ($xml->sitemap as $sitemap) {
            $loc = trim((string) $sitemap->loc);

            if ($loc === '') {
                continue;
            }

            $urls = [
                ...$urls,
                ...$this->resolve($loc, $this->remainingLimit($limit, $urls)),
            ];

            if ($this->limitReached($limit, $urls)) {
                break;
            }
        }

        return $urls;
    }

    /**
     * Resolve URLs from a standard sitemap URL set.
     *
     * @return array<int, string>
     */
    protected function resolveUrlSet(SimpleXMLElement $xml, ?int $limit = null): array
    {
        $urls = [];

        foreach ($xml->url as $url) {
            $loc = trim((string) $url->loc);

            if ($loc !== '') {
                $urls[] = $loc;
            }

            if ($this->limitReached($limit, $urls)) {
                break;
            }
        }

        return $urls;
    }

    /**
     * Calculate how many URLs can still be collected before reaching the limit.
     *
     * @param array<int, string> $urls
     */
    protected function remainingLimit(?int $limit, array $urls): ?int
    {
        return $limit === null
            ? null
            : max(0, $limit - count($urls));
    }

    /**
     * Determine whether the configured URL collection limit has been reached.
     *
     * @param array<int, string> $urls
     */
    protected function limitReached(?int $limit, array $urls): bool
    {
        return $limit !== null && count($urls) >= $limit;
    }
}
