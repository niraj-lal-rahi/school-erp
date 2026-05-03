<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Controllers\Controller;
use App\Models\Documents\Document;
use App\Models\User;
use App\Services\Documents\DocumentPermissionService;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class BaseDocumentController extends Controller
{
    protected function ensureDocumentAbility(User $user, Document $document, string $ability, DocumentPermissionService $permissions): void
    {
        if ($this->hasTenantDocumentOverride($user)) {
            return;
        }

        $allowed = match ($ability) {
            'view' => $permissions->canView($user, $document),
            'download' => $permissions->canDownload($user, $document),
            'update' => $permissions->canUpdate($user, $document),
            'delete' => $permissions->canDelete($user, $document),
            'verify' => $permissions->canVerify($user, $document),
            default => false,
        };

        if (! $allowed) {
            throw new HttpException(403, 'You do not have permission to access this document.');
        }
    }

    protected function hasTenantDocumentOverride(User $user): bool
    {
        if ($user->hasPermission('documents.manage') || $user->hasPermission('documents.verify')) {
            return true;
        }

        return $user->roles()
            ->where(function ($query): void {
                $query->whereIn('roles.code', ['tenant_admin', 'super_admin'])
                    ->orWhereIn('roles.slug', ['school-admin', 'super_admin']);
            })
            ->exists();
    }
}
