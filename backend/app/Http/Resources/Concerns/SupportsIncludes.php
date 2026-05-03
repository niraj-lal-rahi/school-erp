<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Http\Request;

trait SupportsIncludes
{
    protected function includeRequested(Request $request, string $include): bool
    {
        $requested = array_filter(array_map('trim', explode(',', (string) $request->query('include', ''))));

        return in_array($include, $requested, true);
    }

    protected function applySparseFieldset(Request $request, array $payload): array
    {
        $fields = array_filter(array_map('trim', explode(',', (string) $request->query('fields', ''))));

        if ($fields === []) {
            return $payload;
        }

        $allowed = array_flip($fields);
        $filtered = array_intersect_key($payload, $allowed);

        if (array_key_exists('id', $payload)) {
            $filtered['id'] = $payload['id'];
        }

        return $filtered;
    }
}
