import { useMemo } from 'react';
import { usePermission } from '../../../hooks/usePermission';
import { useAppSelector } from '../../../hooks/redux';

export function usePlatformAccess() {
  const { isSuperAdmin, hasAnyPermission, roles, permissions } = usePermission();
  const user = useAppSelector((state) => state.auth.user);

  return useMemo(() => ({
    user,
    roles,
    permissions,
    isSuperAdmin,
    canView: isSuperAdmin || hasAnyPermission(['saas.view', 'settings.manage']),
    canManage: isSuperAdmin,
    canProvision: isSuperAdmin,
    canAudit: isSuperAdmin,
  }), [hasAnyPermission, isSuperAdmin, permissions, roles, user]);
}
