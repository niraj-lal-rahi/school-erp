import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function useCommunicationAccess() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);

  return useMemo(() => ({
    canView: permissions.includes('communication.view'),
    canManage: permissions.includes('communication.manage'),
  }), [permissions]);
}
