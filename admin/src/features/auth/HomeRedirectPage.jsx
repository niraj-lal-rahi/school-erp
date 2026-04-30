import { Navigate } from 'react-router-dom';
import { useAppSelector } from '../../hooks/redux';
import { getDefaultAppRoute } from '../portal/utils/getDefaultAppRoute';

export function HomeRedirectPage() {
  const user = useAppSelector((state) => state.auth.user);

  return <Navigate to={getDefaultAppRoute(user)} replace />;
}
