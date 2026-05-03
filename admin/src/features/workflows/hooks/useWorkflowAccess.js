import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function useWorkflowAccess() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);
  const roles = useAppSelector((state) => state.auth.user?.roles || []);

  return useMemo(() => {
    const roleCodes = roles.map((role) => role.code || role.slug).filter(Boolean);
    const isSuperAdmin = roleCodes.includes('super_admin');
    const isTenantAdmin = roleCodes.includes('tenant_admin') || roleCodes.includes('school-admin');
    const canManage = isSuperAdmin || isTenantAdmin || permissions.includes('workflows.manage');
    const canApprove = canManage || permissions.includes('workflows.approve');
    const canView = canApprove || permissions.includes('workflows.view');

    return {
      permissions,
      roles,
      isSuperAdmin,
      isTenantAdmin,
      canView,
      canManage,
      canApprove,
    };
  }, [permissions, roles]);
}
