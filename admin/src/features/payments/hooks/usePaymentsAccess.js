import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function usePaymentsAccess() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);
  const roles = useAppSelector((state) => state.auth.user?.roles || []);

  return useMemo(() => {
    const roleCodes = roles.map((role) => role.code || role.slug).filter(Boolean);
    const isSuperAdmin = roleCodes.includes('super_admin');
    const isTenantAdmin = roleCodes.includes('tenant_admin') || roleCodes.includes('school-admin');
    const isAccountant = roleCodes.includes('accountant');

    const canView = isSuperAdmin
      || isTenantAdmin
      || isAccountant
      || permissions.includes('finance.view')
      || permissions.includes('payments.view');

    const canManage = isSuperAdmin
      || isTenantAdmin
      || isAccountant
      || permissions.includes('finance.manage')
      || permissions.includes('payments.manage');

    return {
      isSuperAdmin,
      isTenantAdmin,
      isAccountant,
      canView,
      canManage,
      canManageGateways: isSuperAdmin || isTenantAdmin || permissions.includes('payments.gateways.manage'),
      canRefund: canManage || permissions.includes('payments.refund.manage'),
      canReconcile: canManage || permissions.includes('payments.reconcile.manage'),
      canApproveManual: isSuperAdmin || isTenantAdmin || isAccountant || permissions.includes('payments.verify.manage'),
    };
  }, [permissions, roles]);
}
