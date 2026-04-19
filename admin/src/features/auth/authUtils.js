export function flattenPermissions(user) {
  const permissions = new Set();

  (user?.roles || []).forEach((role) => {
    (role.permissions || []).forEach((permission) => {
      if (permission.code) {
        permissions.add(permission.code);
      }
    });
  });

  return Array.from(permissions);
}
