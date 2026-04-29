import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function useReportsAccess() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);

  return useMemo(() => ({
    permissions,
    canView: permissions.includes('reports.view') || permissions.includes('reports.manage'),
    canManage: permissions.includes('reports.manage'),
    canRun: permissions.includes('reports.run') || permissions.includes('reports.manage'),
    canExport: permissions.includes('reports.export') || permissions.includes('reports.manage'),
  }), [permissions]);
}
