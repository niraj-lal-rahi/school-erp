import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function useRbacAccess() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);
  const roles = useAppSelector((state) => state.auth.user?.roles || []);

  return useMemo(() => {
    const roleCodes = roles.map((role) => role.code || role.slug).filter(Boolean);
    const isSuperAdmin = roleCodes.includes('super_admin');

    return {
      permissions,
      roles,
      isSuperAdmin,
      canView: isSuperAdmin || permissions.includes('rbac.view') || permissions.includes('rbac.manage'),
      canManage: isSuperAdmin || permissions.includes('rbac.manage'),
    };
  }, [permissions, roles]);
}
