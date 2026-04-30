import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import FamilyRestroomOutlinedIcon from '@mui/icons-material/FamilyRestroomOutlined';
import SchoolOutlinedIcon from '@mui/icons-material/SchoolOutlined';
import { Alert, Button, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalPageShell } from '../components/PortalPageShell';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext, switchPortalContext } from '../store/portalSlice';

export function ProfileSwitcherPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { context, accessibleStudents, switching, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  const profiles = context?.available_profiles || [];

  async function handleProfileSwitch(profile, studentId = null) {
    const payload = {
      active_profile_type: profile.profile_type,
      active_profile_id: profile.profile_id,
    };

    if (profile.profile_type === 'guardian') {
      payload.active_student_id = studentId || accessibleStudents[0]?.id;
    }

    const result = await dispatch(switchPortalContext(payload));

    if (!result.error) {
      navigate('/portal/dashboard');
    }
  }

  return (
    <PortalPageShell
      title="Profile and Child Switcher"
      description="Choose whether you want to continue as a student or as a guardian, then pick the child whose records should drive the unified portal."
      modeLabel="Context Setup"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        {profiles.map((profile) => (
          <Grid key={profile.id} size={{ xs: 12, lg: 6 }}>
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack spacing={2}>
                <Stack direction="row" spacing={1.5} alignItems="center">
                  {profile.profile_type === 'guardian' ? <FamilyRestroomOutlinedIcon color="primary" /> : <SchoolOutlinedIcon color="primary" />}
                  <Typography variant="h6">
                    {profile.profile_type === 'guardian' ? 'Parent / Guardian Profile' : 'My Student Profile'}
                  </Typography>
                </Stack>

                <Typography color="text.secondary">
                  {profile.profile?.full_name || 'Unnamed profile'}
                </Typography>

                {profile.profile_type === 'student' ? (
                  <Button
                    variant="contained"
                    startIcon={<CheckCircleOutlineOutlinedIcon />}
                    disabled={switching}
                    onClick={() => handleProfileSwitch(profile)}
                  >
                    {switching ? 'Switching...' : 'Continue as Student'}
                  </Button>
                ) : (
                  <Stack spacing={1.25}>
                    <Typography variant="body2" color="text.secondary">
                      Pick which child should be active when you enter guardian mode.
                    </Typography>
                    {accessibleStudents.map((student) => (
                      <Button
                        key={`${profile.id}-${student.id}`}
                        variant="outlined"
                        disabled={switching}
                        onClick={() => handleProfileSwitch(profile, student.id)}
                      >
                        {student.full_name} {student.admission_no ? `(${student.admission_no})` : ''}
                      </Button>
                    ))}
                  </Stack>
                )}
              </Stack>
            </Paper>
          </Grid>
        ))}
      </Grid>
    </PortalPageShell>
  );
}
