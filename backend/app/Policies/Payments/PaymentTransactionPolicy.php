<?php

namespace App\Policies\Payments;

use App\Models\Payments\PaymentTransaction;
use App\Models\Portal\PortalProfileAccess;
use App\Models\Student;
use App\Models\User;

class PaymentTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isFinanceOperator($user) || $this->isPortalUser($user);
    }

    public function view(User $user, PaymentTransaction $transaction): bool
    {
        if (! $this->sameTenant($user, $transaction->school_id)) {
            return false;
        }

        return $this->isFinanceOperator($user) || $this->ownsOrCanAccessStudentPayment($user, $transaction->student_id);
    }

    public function create(User $user, array $attributes = []): bool
    {
        if (! $this->sameTenant($user, (int) ($attributes['school_id'] ?? $user->school_id))) {
            return false;
        }

        if ($this->isFinanceOperator($user)) {
            return true;
        }

        if (($attributes['payable_type'] ?? null) !== 'school_fee') {
            return false;
        }

        return $this->ownsOrCanAccessStudentPayment($user, (int) ($attributes['student_id'] ?? 0), true);
    }

    public function verify(User $user, PaymentTransaction $transaction): bool
    {
        return $this->sameTenant($user, $transaction->school_id)
            && $this->isFinanceOperator($user);
    }

    public function manualApprove(User $user, PaymentTransaction $transaction): bool
    {
        return $this->verify($user, $transaction);
    }

    public function cancel(User $user, PaymentTransaction $transaction): bool
    {
        if (! $this->sameTenant($user, $transaction->school_id)) {
            return false;
        }

        if ($this->isFinanceOperator($user)) {
            return true;
        }

        return in_array($transaction->status, ['pending', 'initiated'], true)
            && $this->ownsOrCanAccessStudentPayment($user, $transaction->student_id, true);
    }

    protected function isFinanceOperator(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('finance.manage')
            || $user->hasPermission('finance.view')
            || $user->roles()
                ->withoutGlobalScopes()
                ->where(function ($query): void {
                    $query->where('roles.code', 'accountant')
                        ->orWhere('roles.slug', 'accountant');
                })
                ->where(function ($query) use ($user): void {
                    $query->whereNull('roles.school_id')
                        ->orWhere('roles.school_id', $user->school_id);
                })
                ->exists();
    }

    protected function isPortalUser(User $user): bool
    {
        return Student::query()
            ->where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->exists()
            || PortalProfileAccess::query()
                ->where('user_id', $user->id)
                ->where('school_id', $user->school_id)
                ->where('status', 'active')
                ->exists();
    }

    protected function ownsOrCanAccessStudentPayment(User $user, ?int $studentId, bool $requirePaymentPermission = false): bool
    {
        if (! $studentId) {
            return false;
        }

        $ownsStudent = Student::query()
            ->where('id', $studentId)
            ->where('school_id', $user->school_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($ownsStudent) {
            return true;
        }

        $query = PortalProfileAccess::query()
            ->where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('student_id', $studentId)
            ->where('status', 'active');

        if ($requirePaymentPermission) {
            $query->where('can_pay_fees', true);
        }

        return $query->exists();
    }

    protected function sameTenant(User $user, int $schoolId): bool
    {
        return (int) $user->school_id === (int) $schoolId;
    }

    protected function isSuperAdmin(User $user): bool
    {
        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'super_admin')
                    ->orWhere('roles.slug', 'super_admin');
            })
            ->exists();
    }

    protected function isTenantAdmin(User $user): bool
    {
        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'tenant_admin')
                    ->orWhere('roles.slug', 'school-admin');
            })
            ->where('roles.school_id', $user->school_id)
            ->exists();
    }
}
