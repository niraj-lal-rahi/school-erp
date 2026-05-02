<?php

namespace App\Http\Requests\Saas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

abstract class SaasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function routeModelId(string $key): ?int
    {
        $value = $this->route($key);

        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function existsGlobal(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column);
    }

    protected function uniqueGlobal(string $table, string $column, ?int $ignoreId = null): Unique
    {
        return Rule::unique($table, $column)->ignore($ignoreId);
    }

    protected function schoolExists(): Exists
    {
        return $this->existsGlobal('schools');
    }

    protected function planExists(): Exists
    {
        return Rule::exists('subscription_plans', 'id')
            ->where(fn ($query) => $query->where('status', 'active'));
    }
}
