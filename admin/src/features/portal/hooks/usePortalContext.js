import { useMemo } from 'react';
import { useAppSelector } from '../../../hooks/redux';

export function usePortalContext() {
  const portalState = useAppSelector((state) => state.portal);
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);

  return useMemo(() => {
    const context = portalState.context;
    const activeContext = context?.active_context || null;
    const activeStudent = activeContext?.active_student || null;
    const activeStudentId = activeContext?.active_student_id || null;
    const isGuardianMode = activeContext?.active_profile_type === 'guardian';
    const isStudentMode = activeContext?.active_profile_type === 'student';

    return {
      ...portalState,
      canViewPortal: permissions.includes('portal.view') || permissions.includes('portal.manage'),
      canManagePortal: permissions.includes('portal.manage'),
      canImpersonatePortal: permissions.includes('portal.impersonate'),
      activeContext,
      activeStudent,
      activeStudentId,
      isGuardianMode,
      isStudentMode,
      hasMultipleProfiles: (context?.available_profiles || []).length > 1,
      hasMultipleStudents: (context?.accessible_students || []).length > 1,
    };
  }, [permissions, portalState]);
}
