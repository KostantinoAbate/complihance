<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use KostantinoAbate\Complihance\Models\CookieScan;

class ScanDiffService
{
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
                'id' => $from->id,
                'uuid' => $from->uuid,
            ],
            'to_scan' => [
                'id' => $to->id,
                'uuid' => $to->uuid,
            ],
            'summary' => [
                'added' => count($addedKeys),
                'removed' => count($removedKeys),
                'changed' => count($changed),
                'unchanged' => count($commonKeys) - count($changed),
            ],
            'added' => array_values(array_map(
                fn (string $key) => $toResults[$key],
                $addedKeys
            )),
            'removed' => array_values(array_map(
                fn (string $key) => $fromResults[$key],
                $removedKeys
            )),
            'changed' => $changed,
        ];
    }

    protected function indexResults(CookieScan $scan): array
    {
        return $scan->results()
            ->get()
            ->mapWithKeys(fn ($result) => [
                $this->logicalKey($result) => [$result->toArray()],
            ])
            ->map(fn (array $items) => $items[0])
            ->all();
    }

    protected function logicalKey($result): string
    {
        return implode('|', [
            $result->type,
            $result->key,
            $result->domain,
            $result->path,
        ]);
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        return $value;
    }
}
