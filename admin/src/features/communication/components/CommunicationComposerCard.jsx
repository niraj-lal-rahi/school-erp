import {
  Alert,
  Button,
  Grid,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useMemo, useState } from 'react';
import { AudienceSelectorFields } from './AudienceSelectorFields';
import { channelOptions, messageTypeOptions, priorityOptions, recipientTypeOptions } from '../types/options';

export function CommunicationComposerCard({
  title,
  description,
  form,
  setForm,
  onSubmit,
  submitting,
  groups = [],
  students = [],
  guardians = [],
  staffMembers = [],
  classes = [],
  sections = [],
  showAudience = false,
}) {
  const [selectedFiles, setSelectedFiles] = useState([]);

  const recipientOptions = useMemo(() => {
    if (form.recipient_type === 'student') {
      return students;
    }

    if (form.recipient_type === 'guardian') {
      return guardians;
    }

    if (form.recipient_type === 'staff') {
      return staffMembers;
    }

    if (form.recipient_type === 'group') {
      return groups;
    }

    return [];
  }, [form.recipient_type, students, guardians, staffMembers, groups]);

  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2.5} component="form" onSubmit={onSubmit}>
        <Stack spacing={0.75}>
          <Typography variant="h5">{title}</Typography>
          <Typography variant="body2" color="text.secondary">
            {description}
          </Typography>
        </Stack>

        {showAudience ? (
          <AudienceSelectorFields
            form={form}
            setForm={setForm}
            classes={classes}
            sections={sections}
            students={students}
            guardians={guardians}
            staffMembers={staffMembers}
          />
        ) : (
          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField
                select
                fullWidth
                label="Recipient Type"
                value={form.recipient_type}
                onChange={(event) => setForm((current) => ({ ...current, recipient_type: event.target.value, recipient_id: '' }))}
              >
                {recipientTypeOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 8 }}>
              <TextField
                select
                fullWidth
                label="Recipient"
                value={form.recipient_id}
                onChange={(event) => setForm((current) => ({ ...current, recipient_id: event.target.value }))}
              >
                <MenuItem value="">Select recipient</MenuItem>
                {recipientOptions.map((item) => (
                  <MenuItem key={item.id} value={item.id}>
                    {item.full_name || item.name || item.title || `#${item.id}`}
                  </MenuItem>
                ))}
              </TextField>
            </Grid>
          </Grid>
        )}

        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField
              fullWidth
              label="Subject"
              value={form.subject}
              onChange={(event) => setForm((current) => ({ ...current, subject: event.target.value }))}
            />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField
              select
              fullWidth
              label="Message Type"
              value={form.message_type}
              onChange={(event) => setForm((current) => ({ ...current, message_type: event.target.value }))}
            >
              {messageTypeOptions.map((option) => (
                <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField
              select
              fullWidth
              label="Priority"
              value={form.priority}
              onChange={(event) => setForm((current) => ({ ...current, priority: event.target.value }))}
            >
              {priorityOptions.map((option) => (
                <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
              ))}
            </TextField>
          </Grid>
        </Grid>

        <TextField
          select
          fullWidth
          SelectProps={{ multiple: true }}
          label="Delivery Channels"
          value={form.channels}
          onChange={(event) => setForm((current) => ({ ...current, channels: event.target.value }))}
        >
          {channelOptions
            .filter((option) => option.value !== 'multi')
            .map((option) => (
              <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
            ))}
        </TextField>

        <TextField
          fullWidth
          multiline
          minRows={6}
          label="Message Body"
          value={form.body}
          onChange={(event) => setForm((current) => ({ ...current, body: event.target.value }))}
        />

        <Stack spacing={1}>
          <Button component="label" variant="outlined">
            Select Attachments
            <input
              hidden
              multiple
              type="file"
              onChange={(event) => setSelectedFiles(Array.from(event.target.files || []))}
            />
          </Button>
          {selectedFiles.length ? (
            <Alert severity="info">
              {selectedFiles.map((file) => file.name).join(', ')}
            </Alert>
          ) : null}
          <Typography variant="caption" color="text.secondary">
            Attachment selection is ready in the UI. The current API contract for this module does not yet accept multipart uploads, so files are not submitted in this step.
          </Typography>
        </Stack>

        <Stack direction="row" justifyContent="flex-end">
          <Button type="submit" variant="contained" disabled={submitting}>
            Send Message
          </Button>
        </Stack>
      </Stack>
    </Paper>
  );
}
