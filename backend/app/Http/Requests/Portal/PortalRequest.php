<?php

namespace App\Http\Requests\Portal;

use App\Models\Guardian;
use App\Models\Student;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

abstract class PortalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function tenantId(): ?int
    {
        return app(TenantContext::class)->id() ?? $this->user()?->school_id;
    }

    protected function existsInTenant(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(
            fn ($query) => $query->where('school_id', $this->tenantId())
        );
    }

    protected function routeModelId(string $key): ?int
    {
        $value = $this->route($key);

        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function guardianOwnsStudent(int $guardianId, int $studentId): bool
    {
        return Guardian::query()
            ->whereKey($guardianId)
            ->whereHas('students', fn ($query) => $query->where('students.id', $studentId))
            ->exists();
    }

    protected function userOwnsStudent(int $studentId): bool
    {
        return Student::query()
            ->whereKey($studentId)
            ->where('user_id', $this->user()?->id)
            ->exists();
    }
}
