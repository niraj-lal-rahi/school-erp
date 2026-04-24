import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, Chip, Grid, IconButton, List, ListItem, ListItemText, Paper, Stack, Switch, TextField, Typography } from '@mui/material';
import { useState } from 'react';

const lifecycleActions = [
  { key: 'activate', label: 'Activate', nextStatus: 'active' },
  { key: 'suspend', label: 'Suspend', nextStatus: 'suspended' },
  { key: 'resign', label: 'Resign', nextStatus: 'resigned' },
  { key: 'terminate', label: 'Terminate', nextStatus: 'terminated' },
  { key: 'retire', label: 'Retire', nextStatus: 'retired' },
];

function SummaryCard({ title, children }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)', height: '100%' }}>
      <Stack spacing={2}>
        <Typography variant="h6">{title}</Typography>
        {children}
      </Stack>
    </Paper>
  );
}

const emptyContact = {
  contact_name: '',
  relationship: '',
  phone: '',
  alternate_phone: '',
  email: '',
  address: '',
  is_primary: true,
};

export function StaffProfileOverviewPanel({ staff, saving, error, onLifecycleSubmit, onCreateEmergencyContact, onDeleteEmergencyContact, readOnly = false }) {
  const [reason, setReason] = useState('');
  const [effectiveDate, setEffectiveDate] = useState('');
  const [contactForm, setContactForm] = useState(emptyContact);

  async function handleContactSubmit(event) {
    event.preventDefault();
    if (!onCreateEmergencyContact) {
      return;
    }

    const result = await onCreateEmergencyContact(contactForm);
    if (!result?.error) {
      setContactForm(emptyContact);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 6 }}>
        <SummaryCard title="Overview">
          <Stack direction="row" spacing={1} flexWrap="wrap">
            <Chip label={staff.current_status} color="primary" />
            <Chip label={staff.staff_type} variant="outlined" />
            <Chip label={staff.employment_type} variant="outlined" />
          </Stack>
          <List dense disablePadding>
            <ListItem disableGutters><ListItemText primary="Employee Code" secondary={staff.employee_code} /></ListItem>
            <ListItem disableGutters><ListItemText primary="Name" secondary={staff.full_name} /></ListItem>
            <ListItem disableGutters><ListItemText primary="Department" secondary={staff.department?.name || 'Not assigned'} /></ListItem>
            <ListItem disableGutters><ListItemText primary="Designation" secondary={staff.designation?.name || 'Not assigned'} /></ListItem>
            <ListItem disableGutters><ListItemText primary="Email" secondary={staff.email || 'Not provided'} /></ListItem>
            <ListItem disableGutters><ListItemText primary="Phone" secondary={staff.phone || 'Not provided'} /></ListItem>
            <ListItem disableGutters><ListItemText primary="Joining Date" secondary={staff.joining_date || 'Not set'} /></ListItem>
            <ListItem disableGutters><ListItemText primary="Address" secondary={[staff.address_line1, staff.city, staff.state, staff.country].filter(Boolean).join(', ') || 'Not provided'} /></ListItem>
          </List>
        </SummaryCard>
      </Grid>

      <Grid size={{ xs: 12, lg: 6 }}>
        <SummaryCard title="Emergency Contacts">
          {(staff.emergency_contacts || []).length ? (
            <List dense disablePadding>
              {staff.emergency_contacts.map((contact) => (
                <ListItem key={contact.id} disableGutters>
                  <ListItemText
                    primary={`${contact.contact_name} (${contact.relationship})`}
                    secondary={`${contact.phone}${contact.alternate_phone ? ` / ${contact.alternate_phone}` : ''}${contact.email ? ` • ${contact.email}` : ''}`}
                  />
                  {!readOnly && onDeleteEmergencyContact ? (
                    <IconButton color="error" onClick={() => onDeleteEmergencyContact(contact.id)}>
                      <DeleteOutlineOutlinedIcon />
                    </IconButton>
                  ) : null}
                </ListItem>
              ))}
            </List>
          ) : (
            <Typography color="text.secondary">No emergency contacts have been saved yet.</Typography>
          )}

          {!readOnly ? (
            <Stack component="form" spacing={2} onSubmit={handleContactSubmit}>
              <Typography variant="subtitle1">Add Emergency Contact</Typography>
              <TextField label="Contact Name" value={contactForm.contact_name} onChange={(event) => setContactForm((current) => ({ ...current, contact_name: event.target.value }))} />
              <TextField label="Relationship" value={contactForm.relationship} onChange={(event) => setContactForm((current) => ({ ...current, relationship: event.target.value }))} />
              <TextField label="Phone" value={contactForm.phone} onChange={(event) => setContactForm((current) => ({ ...current, phone: event.target.value }))} />
              <TextField label="Alternate Phone" value={contactForm.alternate_phone} onChange={(event) => setContactForm((current) => ({ ...current, alternate_phone: event.target.value }))} />
              <TextField label="Email" value={contactForm.email} onChange={(event) => setContactForm((current) => ({ ...current, email: event.target.value }))} />
              <TextField multiline minRows={2} label="Address" value={contactForm.address} onChange={(event) => setContactForm((current) => ({ ...current, address: event.target.value }))} />
              <Stack direction="row" justifyContent="space-between" alignItems="center">
                <Typography variant="body2">Primary Contact</Typography>
                <Switch checked={Boolean(contactForm.is_primary)} onChange={(event) => setContactForm((current) => ({ ...current, is_primary: event.target.checked }))} />
              </Stack>
              <Button type="submit" variant="outlined" disabled={saving}>
                {saving ? 'Saving...' : 'Save Contact'}
              </Button>
            </Stack>
          ) : null}
        </SummaryCard>
      </Grid>

      <Grid size={{ xs: 12 }}>
        <SummaryCard title="Lifecycle Actions">
          <Typography variant="body2" color="text.secondary">
            Use these status transitions for HR-controlled lifecycle events. Each action records a status history entry automatically.
          </Typography>
          {error ? <Alert severity="error">{error}</Alert> : null}
          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 5 }}>
              <TextField
                fullWidth
                label="Reason"
                value={reason}
                onChange={(event) => setReason(event.target.value)}
              />
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField
                fullWidth
                type="date"
                label="Effective Date"
                value={effectiveDate}
                onChange={(event) => setEffectiveDate(event.target.value)}
                InputLabelProps={{ shrink: true }}
              />
            </Grid>
            <Grid size={{ xs: 12, md: 4 }}>
              <Stack direction="row" spacing={1} flexWrap="wrap">
                {lifecycleActions.map((action) => (
                  <Button
                    key={action.key}
                    variant="outlined"
                    size="small"
                    disabled={saving}
                    onClick={() => onLifecycleSubmit(action.key, {
                      action_type: action.key,
                      new_status: action.nextStatus,
                      reason: reason || null,
                      effective_date: effectiveDate || null,
                    })}
                  >
                    {action.label}
                  </Button>
                ))}
              </Stack>
            </Grid>
          </Grid>
        </SummaryCard>
      </Grid>
    </Grid>
  );
}
