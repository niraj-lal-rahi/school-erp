<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

abstract class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('school_id')) {
            $schoolId = $this->user()?->school_id ?? app(\App\Support\TenantContext::class)->id();

            if ($schoolId) {
                $this->merge(['school_id' => $schoolId]);
            }
        }
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

    protected function paymentGatewayExists(?int $schoolId = null): Exists
    {
        return Rule::exists('payment_gateways', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId === null) {
                    return;
                }

                $query->where(function ($tenantQuery) use ($schoolId): void {
                    $tenantQuery->whereNull('school_id')
                        ->orWhere('school_id', $schoolId);
                });
            });
    }

    protected function transactionExists(?int $schoolId = null): Exists
    {
        return Rule::exists('payment_transactions', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function billingExists(?int $schoolId = null): Exists
    {
        return Rule::exists('tenant_billing_records', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function invoiceExists(?int $schoolId = null): Exists
    {
        return Rule::exists('finance_fee_invoices', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function studentExists(?int $schoolId = null): Exists
    {
        return Rule::exists('students', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function userExists(?int $schoolId = null): Exists
    {
        return Rule::exists('users', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function upiVpaRule(): string
    {
        return 'regex:/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/';
    }
}
