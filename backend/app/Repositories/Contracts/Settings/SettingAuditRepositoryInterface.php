<?php

namespace App\Repositories\Contracts\Settings;

use App\Models\Settings\SettingAuditLog;
use Illuminate\Support\Collection;

interface SettingAuditRepositoryInterface
{
    public function list(array $filters = []): Collection;

    public function create(array $attributes): SettingAuditLog;
}
