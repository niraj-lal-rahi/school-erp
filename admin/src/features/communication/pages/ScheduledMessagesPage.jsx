import { Alert, Button, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { AudienceSelectorFields } from '../components/AudienceSelectorFields';
import { CommunicationFormDrawer } from '../components/CommunicationFormDrawer';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { CommunicationStatusBadge } from '../components/CommunicationStatusBadge';
import { useCommunicationAccess } from '../hooks/useCommunicationAccess';
import {
  cancelScheduledMessage,
  createScheduledMessage,
  deleteScheduledMessage,
  fetchCommunicationReferenceData,
  fetchScheduledMessages,
  processDueScheduledMessages,
  updateScheduledMessage,
} from '../store/communicationSlice';
import { channelOptions, statusOptions } from '../types/options';

const initialForm = {
  id: null,
  template_id: '',
  title: '',
  message: '',
  audience_type: 'all',
  class_id: '',
  section_id: '',
  recipient_type: '',
  recipient_id: '',
  individual_recipient_type: 'student',
  channel: 'in_app',
  scheduled_at: '',
  status: 'pending',
};

export function ScheduledMessagesPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useCommunicationAccess();
  const { scheduledMessages, scheduledMessagesPagination, referenceData, loading, saving, error } = useAppSelector((state) => state.communication);
  const [form, setForm] = useState(initialForm);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchCommunicationReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchScheduledMessages({ page, per_page: 10, search, status: statusFilter || undefined }));
  }, [dispatch, page, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      template_id: form.template_id || null,
      class_id: form.class_id || null,
      section_id: form.section_id || null,
    };
    const action = form.id ? updateScheduledMessage({ id: form.id, payload }) : createScheduledMessage(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setDrawerOpen(false);
      setForm(initialForm);
    }
  }

  return (
    <CommunicationPageShell
      title="Scheduled Messages"
      description="Queue future communication bursts, keep send windows organized, and manually process due messages when needed."
      actions={canManage ? (
        <Stack direction="row" spacing={1}>
          <Button variant="outlined" onClick={() => dispatch(processDueScheduledMessages())}>Process Due</Button>
          <Button variant="contained" onClick={() => { setForm(initialForm); setDrawerOpen(true); }}>New Scheduled Message</Button>
        </Stack>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <AppDataTable
        title="Scheduled Queue"
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'audience_type', header: 'Audience' },
          { key: 'channel', header: 'Channel' },
          { key: 'scheduled_at', header: 'Scheduled At' },
          { key: 'status', header: 'Status', render: (row) => <CommunicationStatusBadge value={row.status} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => canManage ? (
              <Stack direction="row" spacing={1}>
                <Button size="small" onClick={() => {
                  setForm({
                    ...initialForm,
                    ...row,
                    template_id: row.template_id || '',
                    class_id: row.class_id || '',
                    section_id: row.section_id || '',
                    scheduled_at: row.scheduled_at ? row.scheduled_at.slice(0, 16) : '',
                  });
                  setDrawerOpen(true);
                }}>
                  Edit
                </Button>
                {row.status === 'pending' ? (
                  <Button size="small" color="warning" onClick={() => dispatch(cancelScheduledMessage(row.id))}>
                    Cancel
                  </Button>
                ) : null}
                <Button size="small" color="error" onClick={() => dispatch(deleteScheduledMessage(row.id))}>Delete</Button>
              </Stack>
            ) : null,
          },
        ]}
        rows={scheduledMessages}
        loading={loading}
        searchValue={search}
        onSearchChange={(value) => {
          setSearch(value);
          setPage(1);
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: statusFilter,
            onChange: (value) => {
              setStatusFilter(value);
              setPage(1);
            },
            options: [{ value: '', label: 'All' }, ...statusOptions.scheduled],
          },
        ]}
        pagination={{ page, totalPages: scheduledMessagesPagination.totalPages, onPageChange: setPage }}
        emptyState="No scheduled messages found."
      />

      <CommunicationFormDrawer
        open={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        title={form.id ? 'Edit Scheduled Message' : 'Create Scheduled Message'}
        description="Set timing, audience, and delivery channel for future communication runs."
        form={form}
        setForm={setForm}
        onSubmit={handleSubmit}
        submitLabel={form.id ? 'Update Scheduled Message' : 'Save Scheduled Message'}
        saving={saving}
        fields={[
          {
            key: 'template_id',
            label: 'Template',
            type: 'select',
            options: [{ value: '', label: 'No Template' }, ...referenceData.templates.map((item) => ({ value: item.id, label: item.name }))],
          },
          { key: 'title', label: 'Title' },
          { key: 'message', label: 'Message', type: 'multiline', rows: 5 },
          {
            key: 'channel',
            label: 'Channel',
            type: 'select',
            options: channelOptions,
          },
          { key: 'scheduled_at', label: 'Scheduled At', type: 'datetime' },
          {
            key: 'status',
            label: 'Status',
            type: 'select',
            options: statusOptions.scheduled,
          },
        ]}
        extraContent={(
          <AudienceSelectorFields
            form={form}
            setForm={setForm}
            classes={referenceData.classes}
            sections={referenceData.sections}
            students={referenceData.students}
            guardians={referenceData.guardians}
            staffMembers={referenceData.staffMembers}
          />
        )}
      />
    </CommunicationPageShell>
  );
}
