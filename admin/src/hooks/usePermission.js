import { useMemo } from 'react';
import { useAppSelector } from './redux';

export function usePermission() {
  const user = useAppSelector((state) => state.auth.user);

  return useMemo(() => {
    const permissions = user?.permissions || [];
    const roles = user?.roles || [];
    const roleCodes = roles.map((role) => role.code || role.slug).filter(Boolean);

    const hasPermission = (permission) => permissions.includes(permission);
    const hasAnyPermission = (expected = []) => expected.some((permission) => permissions.includes(permission));
    const hasRole = (roleCode) => roleCodes.includes(roleCode);
    const hasAnyRole = (expected = []) => expected.some((roleCode) => roleCodes.includes(roleCode));

    return {
      permissions,
      roles: roleCodes,
      hasPermission,
      hasAnyPermission,
      hasRole,
      hasAnyRole,
      isSuperAdmin: hasRole('super_admin'),
      isTenantAdmin: hasAnyRole(['tenant_admin', 'school-admin']),
    };
  }, [user]);
}
