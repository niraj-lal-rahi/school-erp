<?php

namespace App\Services\Workflows;

use App\Models\Workflows\AutomationRule;
use App\Repositories\Contracts\Workflows\AutomationRuleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AutomationRuleService
{
    public function __construct(
        protected AutomationRuleRepositoryInterface $rules,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->rules->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): AutomationRule
    {
        return $this->rules->findOrFail($id);
    }

    public function create(array $attributes): AutomationRule
    {
        $this->validateStructures($attributes);

        return DB::transaction(fn (): AutomationRule => $this->rules->create($attributes));
    }

    public function update(AutomationRule $automationRule, array $attributes): AutomationRule
    {
        $this->validateStructures($attributes);

        return DB::transaction(fn (): AutomationRule => $this->rules->update($automationRule, $attributes));
    }

    public function delete(AutomationRule $automationRule): void
    {
        DB::transaction(function () use ($automationRule): void {
            $this->rules->delete($automationRule);
        });
    }

    public function activate(AutomationRule $automationRule): AutomationRule
    {
        return DB::transaction(fn (): AutomationRule => $this->rules->update($automationRule, [
            'status' => 'active',
        ]));
    }

    public function deactivate(AutomationRule $automationRule): AutomationRule
    {
        return DB::transaction(fn (): AutomationRule => $this->rules->update($automationRule, [
            'status' => 'inactive',
        ]));
    }

    protected function validateStructures(array $attributes): void
    {
        if (array_key_exists('conditions', $attributes) && $attributes['conditions'] !== null && ! is_array($attributes['conditions'])) {
            throw ValidationException::withMessages([
                'conditions' => ['Automation conditions must be a valid JSON object or array.'],
            ]);
        }

        if (array_key_exists('actions', $attributes) && (! is_array($attributes['actions']) || $attributes['actions'] === [])) {
            throw ValidationException::withMessages([
                'actions' => ['At least one automation action is required.'],
            ]);
        }
    }
}
