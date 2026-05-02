import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function useSaasAccess() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);
  const roles = useAppSelector((state) => state.auth.user?.roles || []);
  const user = useAppSelector((state) => state.auth.user);

  return useMemo(() => {
    const roleCodes = roles.map((role) => role.code || role.slug).filter(Boolean);
    const isSuperAdmin = roleCodes.includes('super_admin');
    const isTenantAdmin = roleCodes.includes('tenant_admin') || roleCodes.includes('school-admin');

    return {
      user,
      permissions,
      roles,
      isSuperAdmin,
      isTenantAdmin,
      canView: isSuperAdmin || permissions.includes('saas.view') || isTenantAdmin,
      canManage: isSuperAdmin || permissions.includes('saas.manage'),
      canManagePlans: isSuperAdmin || permissions.includes('saas.plans.manage'),
      canManageBilling: isSuperAdmin || permissions.includes('saas.billing.manage'),
    };
  }, [permissions, roles, user]);
}
