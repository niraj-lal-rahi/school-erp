<?php

namespace App\Services\Documents;

use App\Models\Documents\Document;
use App\Models\Documents\DocumentPermission;
use App\Models\HR\Staff;
use App\Models\Portal\PortalUserProfile;
use App\Models\Student;
use App\Models\User;
use App\Repositories\Contracts\Documents\DocumentPermissionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentPermissionService
{
    public function __construct(
        protected DocumentPermissionRepositoryInterface $permissions,
        protected DocumentAuditService $audits,
    ) {
    }

    public function listByDocument(Document $document): Collection
    {
        return $this->permissions->listByDocument($document->id);
    }

    public function findOrFail(int $id): DocumentPermission
    {
        return $this->permissions->findOrFail($id);
    }

    public function grant(Document $document, array $attributes, ?User $actor = null): DocumentPermission
    {
        return DB::transaction(function () use ($document, $attributes, $actor): DocumentPermission {
            $permission = $this->permissions->create(array_merge($attributes, [
                'school_id' => $document->school_id,
                'document_id' => $document->id,
            ]));

            $this->audits->log($document, 'permission_changed', $actor, ['permission_id' => $permission->id, 'change' => 'granted']);

            return $permission;
        });
    }

    public function update(DocumentPermission $permission, array $attributes, ?User $actor = null): DocumentPermission
    {
        return DB::transaction(function () use ($permission, $attributes, $actor): DocumentPermission {
            $permission = $this->permissions->update($permission, $attributes);
            $this->audits->log($permission->document, 'permission_changed', $actor, ['permission_id' => $permission->id, 'change' => 'updated']);

            return $permission;
        });
    }

    public function revoke(DocumentPermission $permission, ?User $actor = null): void
    {
        DB::transaction(function () use ($permission, $actor): void {
            $document = $permission->document;
            $this->permissions->delete($permission);
            $this->audits->log($document, 'permission_changed', $actor, ['permission_id' => $permission->id, 'change' => 'revoked']);
        });
    }

    public function replace(Document $document, array $permissionRows, ?User $actor = null): Collection
    {
        return DB::transaction(function () use ($document, $permissionRows, $actor): Collection {
            $this->permissions->deleteByDocument($document->id);

            $created = collect($permissionRows)->map(fn (array $row) => $this->permissions->create(array_merge($row, [
                'school_id' => $document->school_id,
                'document_id' => $document->id,
            ])));

            $this->audits->log($document, 'permission_changed', $actor, ['count' => $created->count(), 'change' => 'replaced']);

            return $created;
        });
    }

    public function canView(User $user, Document $document): bool
    {
        return $this->checkAccess($user, $document, 'view');
    }

    public function canDownload(User $user, Document $document): bool
    {
        return $this->checkAccess($user, $document, 'download');
    }

    public function canUpdate(User $user, Document $document): bool
    {
        return $this->checkAccess($user, $document, 'update');
    }

    public function canDelete(User $user, Document $document): bool
    {
        return $this->checkAccess($user, $document, 'delete');
    }

    public function canVerify(User $user, Document $document): bool
    {
        return $this->checkAccess($user, $document, 'verify');
    }

    public function checkAccess(User $user, Document $document, string $ability): bool
    {
        if ($user->school_id !== $document->school_id) {
            return false;
        }

        $column = 'can_'.$ability;
        $permissions = $this->permissions->listByDocument($document->id);

        if ($permissions->isEmpty()) {
            return $ability === 'view';
        }

        foreach ($permissions as $permission) {
            if (! $permission->{$column}) {
                continue;
            }

            if ($permission->permission_type === 'user' && (int) $permission->permission_id === (int) $user->id) {
                return true;
            }

            if ($permission->permission_type === 'role' && $user->roles->contains('id', $permission->permission_id)) {
                return true;
            }

            if ($permission->permission_type === 'owner' && $this->matchesOwner($user, $document)) {
                return true;
            }
        }

        return false;
    }

    protected function matchesOwner(User $user, Document $document): bool
    {
        return match ($document->owner_type) {
            'user' => (int) $document->owner_id === (int) $user->id,
            'tenant' => (int) $document->owner_id === (int) $user->school_id,
            'student' => Student::withoutGlobalScopes()->where('id', $document->owner_id)->where('user_id', $user->id)->exists()
                || PortalUserProfile::withoutGlobalScopes()->where('user_id', $user->id)->where('profile_type', 'student')->where('profile_id', $document->owner_id)->exists(),
            'staff' => Staff::withoutGlobalScopes()->where('id', $document->owner_id)->where('user_id', $user->id)->exists(),
            'guardian' => PortalUserProfile::withoutGlobalScopes()->where('user_id', $user->id)->where('profile_type', 'guardian')->where('profile_id', $document->owner_id)->exists(),
            default => false,
        };
    }
}
