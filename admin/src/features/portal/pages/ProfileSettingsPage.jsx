import { Alert, Grid, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalPageShell } from '../components/PortalPageShell';
import { PortalSectionCard } from '../components/PortalSectionCard';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchAccessibleStudents, fetchPortalContext, fetchPortalProfiles } from '../store/portalSlice';

export function ProfileSettingsPage() {
  const dispatch = useAppDispatch();
  const { context, profiles, accessibleStudents, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
    dispatch(fetchPortalProfiles());
    dispatch(fetchAccessibleStudents());
  }, [dispatch]);

  return (
    <PortalPageShell
      title="Profile Settings"
      description="Review which student and guardian identities are linked to this login, plus which children are accessible through the unified portal."
      modeLabel="Settings"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <PortalSectionCard title="Linked Portal Profiles" subtitle="These profile mappings define which identities this shared login can step into.">
            <Stack spacing={1.25}>
              {profiles.map((profile) => (
                <Typography key={profile.id}>
                  <strong>{profile.profile_type === 'guardian' ? 'Guardian' : 'Student'}:</strong> {profile.profile?.full_name || '-'} {profile.is_default ? '(Default)' : ''}
                </Typography>
              ))}
            </Stack>
          </PortalSectionCard>
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <PortalSectionCard title="Accessible Students" subtitle="A guardian can only move through students listed here, and a student self-profile only sees its own record.">
            <Stack spacing={1.25}>
              {accessibleStudents.map((student) => (
                <Typography key={student.id}>
                  <strong>{student.full_name}</strong> {student.admission_no ? `- ${student.admission_no}` : ''}
                </Typography>
              ))}
            </Stack>
          </PortalSectionCard>
        </Grid>
      </Grid>

      <PortalSectionCard title="Current Context Snapshot" subtitle="The backend session still owns the active portal context, and the frontend mirrors it for a smoother user experience.">
        <Stack spacing={1}>
          <Typography><strong>Profile Mode:</strong> {context?.profile_mode || 'none'}</Typography>
          <Typography><strong>Active Profile Type:</strong> {context?.active_context?.active_profile_type || '-'}</Typography>
          <Typography><strong>Active Student:</strong> {context?.active_context?.active_student?.full_name || '-'}</Typography>
          <Typography color="text.secondary">
            Notification preference editing is not exposed through dedicated portal endpoints yet, so this screen currently focuses on linked identities and active access context.
          </Typography>
        </Stack>
      </PortalSectionCard>
    </PortalPageShell>
  );
}
