<?php

namespace App\Repositories\Eloquent\Settings;

use App\Models\Settings\SettingAuditLog;
use App\Repositories\Contracts\Settings\SettingAuditRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SettingAuditRepository implements SettingAuditRepositoryInterface
{
    public function list(array $filters = []): Collection
    {
        return SettingAuditLog::query()
            ->when($filters['setting_type'] ?? null, fn (Builder $query, string $value) => $query->where('setting_type', $value))
            ->when($filters['setting_key'] ?? null, fn (Builder $query, string $value) => $query->where('setting_key', $value))
            ->latest('id')
            ->get();
    }

    public function create(array $attributes): SettingAuditLog
    {
        return SettingAuditLog::withoutGlobalScopes()->create($attributes);
    }
}
