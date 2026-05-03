import { useMemo } from 'react';
import { usePermission } from '../../../hooks/usePermission';

export function usePaymentsAccess() {
  const {
    permissions,
    roles,
    isSuperAdmin,
    isTenantAdmin,
    hasRole,
  } = usePermission();

  return useMemo(() => {
    const isAccountant = hasRole('accountant');

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
  }, [hasRole, isSuperAdmin, isTenantAdmin, permissions, roles]);
}
