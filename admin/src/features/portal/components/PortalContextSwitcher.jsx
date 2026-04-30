import CompareArrowsOutlinedIcon from '@mui/icons-material/CompareArrowsOutlined';
import FamilyRestroomOutlinedIcon from '@mui/icons-material/FamilyRestroomOutlined';
import SchoolOutlinedIcon from '@mui/icons-material/SchoolOutlined';
import {
  Alert,
  Button,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch } from '../../../hooks/redux';
import { switchPortalContext } from '../store/portalSlice';
import { usePortalContext } from '../hooks/usePortalContext';

function profileLabel(profile) {
  if (profile.profile_type === 'student') {
    return `My Student Profile - ${profile.profile?.full_name || 'Student'}`;
  }

  return `Parent / Guardian Profile - ${profile.profile?.full_name || 'Guardian'}`;
}

export function PortalContextSwitcher() {
  const dispatch = useAppDispatch();
  const {
    context,
    accessibleStudents,
    activeContext,
    switching,
    error,
  } = usePortalContext();

  const availableProfiles = context?.available_profiles || [];

  const [selectedProfileKey, setSelectedProfileKey] = useState('');
  const [selectedStudentId, setSelectedStudentId] = useState('');

  useEffect(() => {
    if (!activeContext) {
      return;
    }

    setSelectedProfileKey(`${activeContext.active_profile_type}:${activeContext.active_profile_id}`);
    setSelectedStudentId(activeContext.active_student_id ? String(activeContext.active_student_id) : '');
  }, [activeContext]);

  const selectedProfile = useMemo(
    () => availableProfiles.find((profile) => `${profile.profile_type}:${profile.profile_id}` === selectedProfileKey) || null,
    [availableProfiles, selectedProfileKey],
  );

  async function handleApply() {
    if (!selectedProfile) {
      return;
    }

    const payload = {
      active_profile_type: selectedProfile.profile_type,
      active_profile_id: selectedProfile.profile_id,
    };

    if (selectedProfile.profile_type === 'guardian') {
      payload.active_student_id = Number(selectedStudentId || activeContext?.active_student_id || 0);
    }

    await dispatch(switchPortalContext(payload));
  }

  return (
    <Paper elevation={0} sx={{ p: 2.25, border: '1px solid rgba(20,33,61,0.08)', backgroundColor: 'rgba(255,255,255,0.76)' }}>
      <Stack spacing={2}>
        <Stack direction={{ xs: 'column', lg: 'row' }} spacing={2} justifyContent="space-between" alignItems={{ xs: 'flex-start', lg: 'center' }}>
          <Stack spacing={0.5}>
            <Typography variant="subtitle1">Profile Context</Typography>
            <Typography variant="body2" color="text.secondary">
              Switch between your student identity and guardian view without leaving the same portal.
            </Typography>
          </Stack>
          <Stack direction="row" spacing={1} flexWrap="wrap">
            {activeContext?.active_profile_type ? (
              <Button
                size="small"
                variant="outlined"
                startIcon={activeContext.active_profile_type === 'guardian' ? <FamilyRestroomOutlinedIcon /> : <SchoolOutlinedIcon />}
                disabled
              >
                {activeContext.active_profile_type === 'guardian' ? 'Guardian Mode' : 'Student Mode'}
              </Button>
            ) : null}
          </Stack>
        </Stack>

        {error ? <Alert severity="error">{error}</Alert> : null}

        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
          <TextField
            select
            fullWidth
            label="Active Profile"
            value={selectedProfileKey}
            onChange={(event) => {
              const nextKey = event.target.value;
              setSelectedProfileKey(nextKey);
              const nextProfile = availableProfiles.find((profile) => `${profile.profile_type}:${profile.profile_id}` === nextKey);

              if (nextProfile?.profile_type === 'student') {
                setSelectedStudentId(String(nextProfile.profile_id));
                return;
              }

              if (!selectedStudentId && accessibleStudents[0]) {
                setSelectedStudentId(String(accessibleStudents[0].id));
              }
            }}
          >
            {availableProfiles.map((profile) => (
              <MenuItem key={`${profile.profile_type}:${profile.profile_id}`} value={`${profile.profile_type}:${profile.profile_id}`}>
                {profileLabel(profile)}
              </MenuItem>
            ))}
          </TextField>

          <TextField
            select
            fullWidth
            disabled={selectedProfile?.profile_type !== 'guardian'}
            label="Selected Child"
            value={selectedProfile?.profile_type === 'guardian' ? selectedStudentId : ''}
            onChange={(event) => setSelectedStudentId(event.target.value)}
            helperText={selectedProfile?.profile_type === 'guardian' ? 'Choose the child whose data you want to open in the portal.' : 'Child selection is available only in guardian mode.'}
          >
            {accessibleStudents.map((student) => (
              <MenuItem key={student.id} value={String(student.id)}>
                {student.full_name} {student.admission_no ? `(${student.admission_no})` : ''}
              </MenuItem>
            ))}
          </TextField>

          <Button
            variant="contained"
            startIcon={<CompareArrowsOutlinedIcon />}
            disabled={switching || !selectedProfile}
            onClick={handleApply}
            sx={{ minWidth: { md: 180 } }}
          >
            {switching ? 'Switching...' : 'Apply Context'}
          </Button>
        </Stack>
      </Stack>
    </Paper>
  );
}
