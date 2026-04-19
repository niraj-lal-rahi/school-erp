import { useEffect } from 'react';
import { RouterProvider } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from './hooks/redux';
import { bootstrapSession, clearSession } from './features/auth/authSlice';
import { router } from './routes/router';
import { clearStoredSession } from './utils/tokenStorage';

export function App() {
  const dispatch = useAppDispatch();
  const accessToken = useAppSelector((state) => state.auth.accessToken);

  useEffect(() => {
    if (!accessToken) {
      dispatch(clearSession());
      return;
    }

    dispatch(bootstrapSession()).unwrap().catch(() => {
      clearStoredSession();
      dispatch(clearSession());
    });
  }, [accessToken, dispatch]);

  return <RouterProvider router={router} />;
}
