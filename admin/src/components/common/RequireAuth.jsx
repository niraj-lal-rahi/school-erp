import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAppSelector } from '../../hooks/redux';
import { FullScreenLoader } from './FullScreenLoader';

export function RequireAuth() {
  const location = useLocation();
  const { accessToken, initialized, loading } = useAppSelector((state) => state.auth);

  if (!initialized || loading) {
    return <FullScreenLoader />;
  }

  if (!accessToken) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  return <Outlet />;
}
