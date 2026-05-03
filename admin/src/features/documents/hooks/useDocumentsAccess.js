import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function useDocumentsAccess() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);
  const roles = useAppSelector((state) => state.auth.user?.roles || []);

  return useMemo(() => {
    const roleCodes = roles.map((role) => role.code || role.slug).filter(Boolean);
    const isSuperAdmin = roleCodes.includes('super_admin');
    const isTenantAdmin = roleCodes.includes('tenant_admin') || roleCodes.includes('school-admin');
    const canManage = isSuperAdmin || isTenantAdmin || permissions.includes('documents.manage');
    const canVerify = canManage || permissions.includes('documents.verify');
    const canView = canVerify || permissions.includes('documents.view');

    return {
      permissions,
      roles,
      isSuperAdmin,
      isTenantAdmin,
      canView,
      canManage,
      canVerify,
    };
  }, [permissions, roles]);
}
