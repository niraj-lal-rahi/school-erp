import { Alert, Button, Stack, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { AudienceSelectorFields } from '../components/AudienceSelectorFields';
import { CommunicationFormDrawer } from '../components/CommunicationFormDrawer';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { CommunicationStatusBadge } from '../components/CommunicationStatusBadge';
import { useCommunicationAccess } from '../hooks/useCommunicationAccess';
import {
  cancelAnnouncement,
  createAnnouncement,
  deleteAnnouncement,
  fetchAnnouncementRecipients,
  fetchAnnouncements,
  fetchCommunicationReferenceData,
  publishAnnouncement,
  updateAnnouncement,
} from '../store/communicationSlice';
import { announcementTypeOptions, audienceOptions, priorityOptions, statusOptions } from '../types/options';

const initialForm = {
  id: null,
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

export function AnnouncementsPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useCommunicationAccess();
  const {
    announcements,
    announcementsPagination,
    announcementRecipients,
    referenceData,
    loading,
    saving,
    error,
  } = useAppSelector((state) => state.communication);
  const [form, setForm] = useState(initialForm);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [audienceFilter, setAudienceFilter] = useState('');
  const [page, setPage] = useState(1);
  const [selectedAnnouncement, setSelectedAnnouncement] = useState(null);

  useEffect(() => {
    dispatch(fetchCommunicationReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchAnnouncements({
      page,
      per_page: 10,
      search,
      status: statusFilter || undefined,
      audience_type: audienceFilter || undefined,
    }));
  }, [dispatch, page, search, statusFilter, audienceFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      academic_year_id: form.academic_year_id || null,
      class_id: form.class_id || null,
      section_id: form.section_id || null,
      recipient_type: form.audience_type === 'individual' ? form.individual_recipient_type : null,
      recipient_id: form.audience_type === 'individual' ? form.recipient_id || null : null,
      publish_at: form.publish_at || null,
      expires_at: form.expires_at || null,
    };

    const action = form.id
      ? updateAnnouncement({ id: form.id, payload })
      : createAnnouncement(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setDrawerOpen(false);
      setForm(initialForm);
    }
  }

  const recipientSummary = useMemo(() => {
    if (!selectedAnnouncement) {
      return [];
    }

    return announcementRecipients.slice(0, 10);
  }, [announcementRecipients, selectedAnnouncement]);

  return (
    <CommunicationPageShell
      title="Announcement Management"
      description="Create, schedule, publish, and track announcements across students, parents, staff, and targeted academic groups."
      actions={canManage ? (
        <Button
          variant="contained"
          onClick={() => {
            setForm(initialForm);
            setDrawerOpen(true);
          }}
        >
          New Announcement
        </Button>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Announcements"
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'announcement_type', header: 'Type', render: (row) => row.announcement_type },
          { key: 'audience_type', header: 'Audience' },
          { key: 'priority', header: 'Priority' },
          { key: 'status', header: 'Status', render: (row) => <CommunicationStatusBadge value={row.status} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1} flexWrap="wrap">
                <Button size="small" onClick={() => {
                  setSelectedAnnouncement(row);
                  dispatch(fetchAnnouncementRecipients(row.id));
                }}>
                  Recipients
                </Button>
                {canManage ? (
                  <>
                    <Button size="small" onClick={() => {
                      setForm({
                        ...initialForm,
                        ...row,
                        academic_year_id: row.academic_year_id || '',
                        class_id: row.class_id || '',
                        section_id: row.section_id || '',
                        publish_at: row.publish_at ? row.publish_at.slice(0, 16) : '',
                        expires_at: row.expires_at ? row.expires_at.slice(0, 16) : '',
                      });
                      setDrawerOpen(true);
                    }}>
                      Edit
                    </Button>
                    {row.status !== 'published' ? (
                      <Button size="small" onClick={() => dispatch(publishAnnouncement({ id: row.id, payload: {} }))}>
                        Publish
                      </Button>
                    ) : null}
                    {row.status === 'published' || row.status === 'scheduled' ? (
                      <Button size="small" color="warning" onClick={() => dispatch(cancelAnnouncement(row.id))}>
                        Cancel
                      </Button>
                    ) : null}
                    <Button
                      size="small"
                      color="error"
                      onClick={() => {
                        if (window.confirm('Delete this announcement?')) {
                          dispatch(deleteAnnouncement(row.id));
                        }
                      }}
                    >
                      Delete
                    </Button>
                  </>
                ) : null}
              </Stack>
            ),
          },
        ]}
        rows={announcements}
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
            options: [{ value: '', label: 'All' }, ...statusOptions.announcement],
          },
          {
            key: 'audience_type',
            label: 'Audience',
            value: audienceFilter,
            onChange: (value) => {
              setAudienceFilter(value);
              setPage(1);
            },
            options: [{ value: '', label: 'All' }, ...audienceOptions],
          },
        ]}
        pagination={{ page, totalPages: announcementsPagination.totalPages, onPageChange: setPage }}
        emptyState="No announcements available yet."
      />

      {selectedAnnouncement ? (
        <Stack spacing={1}>
          <Typography variant="h6">Recipient Snapshot: {selectedAnnouncement.title}</Typography>
          {recipientSummary.length ? recipientSummary.map((recipient) => (
            <Typography key={recipient.id} variant="body2" color="text.secondary">
              {recipient.recipient_type} #{recipient.recipient_id}
            </Typography>
          )) : (
            <Typography variant="body2" color="text.secondary">
              No recipients loaded for this announcement yet.
            </Typography>
          )}
        </Stack>
      ) : null}

      <CommunicationFormDrawer
        open={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        title={form.id ? 'Edit Announcement' : 'Create Announcement'}
        description="Keep the core announcement flow close at hand for quick publishing and schedule changes."
        form={form}
        setForm={setForm}
        onSubmit={handleSubmit}
        submitLabel={form.id ? 'Update Announcement' : 'Save Announcement'}
        saving={saving}
        fields={[
          {
            key: 'academic_year_id',
            label: 'Academic Year',
            type: 'select',
            options: [{ value: '', label: 'All Years' }, ...referenceData.academicYears.map((item) => ({ value: item.id, label: item.name }))],
          },
          { key: 'title', label: 'Title' },
          {
            key: 'announcement_type',
            label: 'Announcement Type',
            type: 'select',
            options: announcementTypeOptions,
          },
          {
            key: 'priority',
            label: 'Priority',
            type: 'select',
            options: priorityOptions,
          },
          {
            key: 'status',
            label: 'Status',
            type: 'select',
            options: statusOptions.announcement,
          },
          { key: 'publish_at', label: 'Publish At', type: 'datetime' },
          { key: 'expires_at', label: 'Expires At', type: 'datetime' },
          { key: 'content', label: 'Content', type: 'multiline', rows: 5 },
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
