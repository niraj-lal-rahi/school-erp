import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function useSettingsAccess() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);
  const roles = useAppSelector((state) => state.auth.user?.roles || []);

  return useMemo(() => {
    const roleCodes = roles.map((role) => role.code || role.slug).filter(Boolean);
    const isSuperAdmin = roleCodes.includes('super_admin');
    const isTenantAdmin = roleCodes.includes('tenant_admin') || roleCodes.includes('school-admin');

    const canView = isSuperAdmin
      || isTenantAdmin
      || permissions.includes('settings.view')
      || permissions.includes('settings.manage');

    const canManage = isSuperAdmin
      || isTenantAdmin
      || permissions.includes('settings.manage');

    return {
      isSuperAdmin,
      isTenantAdmin,
      canView,
      canManage,
      canManageGlobal: isSuperAdmin,
      canSeeAudit: canManage,
      canEditSensitive: canManage,
    };
  }, [permissions, roles]);
}
