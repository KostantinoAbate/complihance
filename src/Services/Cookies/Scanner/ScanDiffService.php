<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use DateTimeInterface;
use KostantinoAbate\Complihance\Models\CookieScan;
use KostantinoAbate\Complihance\Models\CookieScanResult;

class ScanDiffService
{
    /**
     * Compare two cookie scans and return added, removed, changed, and unchanged items.
     *
     * @return array<string, mixed>
     */
    public function diff(CookieScan $from, CookieScan $to, bool $includeVolatile = false): array
    {
        $fromResults = $this->indexResults($from);
        $toResults = $this->indexResults($to);

        $fromKeys = array_keys($fromResults);
        $toKeys = array_keys($toResults);

        $addedKeys = array_diff($toKeys, $fromKeys);
        $removedKeys = array_diff($fromKeys, $toKeys);
        $commonKeys = array_intersect($fromKeys, $toKeys);

        $changed = [];

        foreach ($commonKeys as $key) {
            $fromResult = $fromResults[$key];
            $toResult = $toResults[$key];

            $changes = [];

            $fields = ['vendor', 'category', 'secure', 'http_only', 'same_site'];

            if ($includeVolatile) {
                $fields[] = 'expires_at';
            }

            foreach ($fields as $field) {
                $fromValue = $this->normalizeValue($fromResult[$field] ?? null);
                $toValue = $this->normalizeValue($toResult[$field] ?? null);

                if ($fromValue !== $toValue) {
                    $changes[$field] = [
                        'from' => $fromValue,
                        'to' => $toValue,
                    ];
                }
            }

            if ($changes !== []) {
                $changed[] = [
                    'key' => $key,
                    'type' => $toResult['type'] ?? null,
                    'item' => $toResult,
                    'changes' => $changes,
                ];
            }
        }

        return [
            'from_scan' => [
                'id' => $from->getKey(),
                'uuid' => $from->getAttribute('uuid'),
            ],
            'to_scan' => [
                'id' => $to->getKey(),
                'uuid' => $to->getAttribute('uuid'),
            ],
            'summary' => [
                'added' => count($addedKeys),
                'removed' => count($removedKeys),
                'changed' => count($changed),
                'unchanged' => count($commonKeys) - count($changed),
            ],
            'added' => array_values(array_map(
                fn (string $key): array => $toResults[$key],
                $addedKeys
            )),
            'removed' => array_values(array_map(
                fn (string $key): array => $fromResults[$key],
                $removedKeys
            )),
            'changed' => $changed,
        ];
    }

    /**
     * Index scan results by their logical comparison key.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function indexResults(CookieScan $scan): array
    {
        return $scan->results()
            ->get()
            ->mapWithKeys(fn (CookieScanResult $result): array => [
                $this->logicalKey($result) => [$result->toArray()],
            ])
            ->map(fn (array $items): array => $items[0])
            ->all();
    }

    /**
     * Build the logical key used to compare scan results.
     */
    protected function logicalKey(CookieScanResult $result): string
    {
        return implode('|', [
            $result->getAttribute('type'),
            $result->getAttribute('key'),
            $result->getAttribute('domain'),
            $result->getAttribute('path'),
        ]);
    }

    /**
     * Normalize values before comparing them.
     */
    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        return $value;
    }
}
