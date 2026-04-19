import { Alert, Button, CircularProgress, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchAdmissionById, runAdmissionAction } from '../store/admissionSlice';

export function AdmissionReviewPage() {
  const { admissionId } = useParams();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { currentAdmission, loading, saving, error } = useAppSelector((state) => state.admissions);
  const [remarks, setRemarks] = useState('');
  const [conversion, setConversion] = useState({
    admission_no: '',
    roll_no: '',
    joining_date: '',
    section_id: '',
    current_status: 'active',
  });

  useEffect(() => {
    dispatch(fetchAdmissionById(admissionId));
  }, [dispatch, admissionId]);

  useEffect(() => {
    if (currentAdmission) {
      setRemarks(currentAdmission.remarks || '');
      setConversion((current) => ({
        ...current,
        section_id: currentAdmission.section_id || '',
      }));
    }
  }, [currentAdmission]);

  async function handleWorkflow(action, payload = {}) {
    const result = await dispatch(runAdmissionAction({ action, admissionId, payload }));
    if (!result.error && action === 'convertAdmission') {
      navigate('/students');
    }
  }

  if (loading || !currentAdmission) {
    return <Stack alignItems="center" py={8}><CircularProgress /></Stack>;
  }

  return (
    <Stack spacing={3}>
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Typography variant="h5">{currentAdmission.full_name}</Typography>
          <Typography color="text.secondary">
            {currentAdmission.application_no} • {currentAdmission.application_status}
          </Typography>
          <Typography>
            Guardian: {currentAdmission.guardian_name} ({currentAdmission.guardian_phone})
          </Typography>
          <Typography>
            Applied Class: {currentAdmission.class?.name || 'Not selected'}
          </Typography>
          <TextField
            fullWidth
            multiline
            minRows={3}
            label="Review Remarks"
            value={remarks}
            onChange={(event) => setRemarks(event.target.value)}
          />
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <Button variant="outlined" disabled={saving} onClick={() => handleWorkflow('submitAdmission')}>
              Submit
            </Button>
            <Button variant="outlined" disabled={saving} onClick={() => handleWorkflow('reviewAdmission', { remarks })}>
              Mark Under Review
            </Button>
            <Button variant="contained" disabled={saving} onClick={() => handleWorkflow('approveAdmission', { remarks })}>
              Approve
            </Button>
            <Button color="warning" variant="outlined" disabled={saving} onClick={() => handleWorkflow('waitlistAdmission', { remarks })}>
              Waitlist
            </Button>
            <Button color="error" variant="outlined" disabled={saving} onClick={() => handleWorkflow('rejectAdmission', { remarks })}>
              Reject
            </Button>
          </Stack>
        </Stack>
      </Paper>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Typography variant="h6">Convert To Student</Typography>
          <TextField label="Admission No" value={conversion.admission_no} onChange={(event) => setConversion((current) => ({ ...current, admission_no: event.target.value }))} />
          <TextField label="Roll No" value={conversion.roll_no} onChange={(event) => setConversion((current) => ({ ...current, roll_no: event.target.value }))} />
          <TextField type="date" label="Joining Date" value={conversion.joining_date} onChange={(event) => setConversion((current) => ({ ...current, joining_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
          <Button
            variant="contained"
            disabled={saving || currentAdmission.application_status !== 'approved'}
            onClick={() => handleWorkflow('convertAdmission', conversion)}
          >
            Convert To Student
          </Button>
        </Stack>
      </Paper>
    </Stack>
  );
}
