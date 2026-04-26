import { Alert, Button, Grid, MenuItem, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { AudienceSelectorFields } from '../components/AudienceSelectorFields';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { useCommunicationAccess } from '../hooks/useCommunicationAccess';
import { createAnnouncement, fetchCommunicationReferenceData } from '../store/communicationSlice';
import { announcementTypeOptions, priorityOptions } from '../types/options';

const initialForm = {
  academic_year_id: '',
  title: '',
  content: '',
  announcement_type: 'general',
  audience_type: 'all',
  class_id: '',
  section_id: '',
  recipient_type: '',
  recipient_id: '',
  individual_recipient_type: 'student',
  publish_at: '',
  expires_at: '',
  priority: 'normal',
  status: 'draft',
};

export function CreateAnnouncementPage() {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { canManage } = useCommunicationAccess();
  const { referenceData, saving, error } = useAppSelector((state) => state.communication);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchCommunicationReferenceData());
  }, [dispatch]);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await dispatch(createAnnouncement({
      ...form,
      academic_year_id: form.academic_year_id || null,
      class_id: form.class_id || null,
      section_id: form.section_id || null,
      recipient_type: form.audience_type === 'individual' ? form.individual_recipient_type : null,
      recipient_id: form.audience_type === 'individual' ? form.recipient_id || null : null,
      publish_at: form.publish_at || null,
      expires_at: form.expires_at || null,
    }));

    if (!result.error) {
      navigate('/communication/announcements');
    }
  }

  return (
    <CommunicationPageShell
      title="Create Announcement"
      description="Draft a targeted announcement with scheduling, audience filters, and priority controls before it goes live."
    >
      {canManage ? (
        <Stack component="form" spacing={3} onSubmit={handleSubmit}>
          {error ? <Alert severity="error">{error}</Alert> : null}
          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField fullWidth label="Title" value={form.title} onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))} />
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField select fullWidth label="Type" value={form.announcement_type} onChange={(event) => setForm((current) => ({ ...current, announcement_type: event.target.value }))}>
                {announcementTypeOptions.map((option) => <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>)}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField select fullWidth label="Priority" value={form.priority} onChange={(event) => setForm((current) => ({ ...current, priority: event.target.value }))}>
                {priorityOptions.map((option) => <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>)}
              </TextField>
            </Grid>
          </Grid>

          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField select fullWidth label="Academic Year" value={form.academic_year_id} onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value }))}>
                <MenuItem value="">All Years</MenuItem>
                {referenceData.academicYears.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField fullWidth label="Publish At" type="datetime-local" InputLabelProps={{ shrink: true }} value={form.publish_at} onChange={(event) => setForm((current) => ({ ...current, publish_at: event.target.value }))} />
            </Grid>
          </Grid>

          <AudienceSelectorFields
            form={form}
            setForm={setForm}
            classes={referenceData.classes}
            sections={referenceData.sections}
            students={referenceData.students}
            guardians={referenceData.guardians}
            staffMembers={referenceData.staffMembers}
          />

          <TextField
            fullWidth
            multiline
            minRows={8}
            label="Announcement Content"
            value={form.content}
            onChange={(event) => setForm((current) => ({ ...current, content: event.target.value }))}
          />

          <Stack direction="row" spacing={1.5} justifyContent="flex-end">
            <Button variant="text" onClick={() => navigate('/communication/announcements')}>Back</Button>
            <Button type="submit" variant="contained" disabled={saving}>Save Announcement</Button>
          </Stack>
        </Stack>
      ) : (
        <Typography color="text.secondary">You do not have permission to create announcements.</Typography>
      )}
    </CommunicationPageShell>
  );
}
