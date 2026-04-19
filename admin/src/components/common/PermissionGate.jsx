import { Alert } from '@mui/material';
import { useAppSelector } from '../../hooks/redux';

export function PermissionGate({ permission, fallback = null, children }) {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);

  if (!permissions.includes(permission)) {
    return fallback ?? <Alert severity="warning">You do not have access to this action.</Alert>;
  }

  return children;
}
