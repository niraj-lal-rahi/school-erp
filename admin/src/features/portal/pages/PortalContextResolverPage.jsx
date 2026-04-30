import { Alert, Button, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { FullScreenLoader } from '../../../components/common/FullScreenLoader';
import { useAppDispatch } from '../../../hooks/redux';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext } from '../store/portalSlice';

export function PortalContextResolverPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { canViewPortal, context, loading, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (!context) {
      return;
    }

    if (context.active_context?.active_student_id) {
      navigate('/portal/dashboard', { replace: true });
      return;
    }

    if ((context.available_profiles || []).length > 1 || (context.accessible_students || []).length > 1) {
      navigate('/portal/switcher', { replace: true });
      return;
    }
  }, [context, navigate]);

  if (!canViewPortal) {
    return <Navigate to="/dashboard" replace />;
  }

  if (loading) {
    return <FullScreenLoader />;
  }

  if (!context?.available_profiles?.length) {
    return (
      <Paper elevation={0} sx={{ p: 4, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Typography variant="h5">Portal Access Not Ready</Typography>
          <Typography color="text.secondary">
            This login is valid, but no student or guardian profile has been linked to it yet. Once a portal profile is mapped, the unified student and parent experience will appear automatically.
          </Typography>
          {error ? <Alert severity="error">{error}</Alert> : null}
          <Button variant="outlined" onClick={() => dispatch(fetchPortalContext())}>
            Retry Context Load
          </Button>
        </Stack>
      </Paper>
    );
  }

  return <FullScreenLoader />;
}
