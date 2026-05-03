<?php

namespace App\Repositories\Contracts\Workflows;

use App\Models\Workflows\ApprovalRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ApprovalRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): ApprovalRequest;

    public function create(array $attributes): ApprovalRequest;

    public function update(ApprovalRequest $approvalRequest, array $attributes): ApprovalRequest;

    public function delete(ApprovalRequest $approvalRequest): void;
}
