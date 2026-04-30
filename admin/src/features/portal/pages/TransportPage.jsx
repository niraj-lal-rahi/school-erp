import { Alert, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalPageShell } from '../components/PortalPageShell';
import { PortalSectionCard } from '../components/PortalSectionCard';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext, fetchPortalTransport } from '../store/portalSlice';

export function TransportPage() {
  const dispatch = useAppDispatch();
  const { activeStudentId, transport, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (activeStudentId) {
      dispatch(fetchPortalTransport(activeStudentId));
    }
  }, [activeStudentId, dispatch]);

  const allocation = transport?.allocation;

  return (
    <PortalPageShell
      title="Transport"
      description="Keep route, vehicle, stop, and fare information close at hand for students and parents using the same shared portal context."
      modeLabel="Transport"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <PortalSectionCard title="Transport Allocation" subtitle="The active transport setup for the selected student.">
        {allocation ? (
          <Stack spacing={1}>
            <Typography><strong>Route:</strong> {allocation.route?.name || '-'} {allocation.route?.code ? `(${allocation.route.code})` : ''}</Typography>
            <Typography><strong>Vehicle:</strong> {allocation.vehicle?.vehicle_no || '-'} {allocation.vehicle?.registration_no ? `(${allocation.vehicle.registration_no})` : ''}</Typography>
            <Typography><strong>Pickup Stop:</strong> {allocation.pickup_stop || '-'}</Typography>
            <Typography><strong>Drop Stop:</strong> {allocation.drop_stop || '-'}</Typography>
            <Typography><strong>Fare:</strong> ₹{Number(allocation.fare_amount || 0).toFixed(2)}</Typography>
            <Typography><strong>Allocated From:</strong> {allocation.allocated_from || '-'}</Typography>
            <Typography><strong>Allocated To:</strong> {allocation.allocated_to || '-'}</Typography>
          </Stack>
        ) : (
          <Typography color="text.secondary">No active transport allocation exists for the selected student.</Typography>
        )}
      </PortalSectionCard>
    </PortalPageShell>
  );
}
