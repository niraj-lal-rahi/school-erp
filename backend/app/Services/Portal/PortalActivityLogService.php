<?php

namespace App\Services\Portal;

use App\Models\User;
use App\Repositories\Contracts\Portal\PortalActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PortalActivityLogService
{
    public function __construct(
        protected PortalActivityLogRepositoryInterface $activityLogs,
    ) {
    }

    public function log(User $user, string $action, array $context = [])
    {
        return $this->activityLogs->create([
            'school_id' => $user->school_id,
            'user_id' => $user->id,
            'student_id' => $context['student_id'] ?? null,
            'action' => $action,
            'description' => $context['description'] ?? null,
            'metadata' => $context['metadata'] ?? null,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->activityLogs->paginate($filters, $perPage);
    }
}
