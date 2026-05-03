export function getDefaultAppRoute(user) {
  const permissions = user?.permissions || [];
  const roles = user?.roles || [];
  const roleCodes = roles.map((role) => role.code || role.slug).filter(Boolean);

  if (roleCodes.includes('super_admin')) {
    return '/platform/tenants';
  }

  const hasPortalOnlyAccess = permissions.includes('portal.view')
    && !permissions.some((permission) => [
      'students.view',
      'academic-management.view',
      'hr.view',
      'finance.view',
      'attendance.view',
      'timetable.view',
      'transport.view',
      'communication.view',
      'exams.view',
      'reports.view',
    ].includes(permission));

  if (hasPortalOnlyAccess) {
    return '/portal';
  }

  return permissions.includes('students.view') ? '/students' : '/dashboard';
}
