import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationFormDrawer } from '../components/CommunicationFormDrawer';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { CommunicationStatusBadge } from '../components/CommunicationStatusBadge';
import { useCommunicationAccess } from '../hooks/useCommunicationAccess';
import {
  addGroupMember,
  createGroup,
  deleteGroup,
  fetchCommunicationReferenceData,
  fetchGroups,
  removeGroupMember,
  updateGroup,
} from '../store/communicationSlice';
import { groupTypeOptions, participantTypeOptions, statusOptions } from '../types/options';

const initialForm = {
  id: null,
  name: '',
  code: '',
  group_type: 'custom',
  class_id: '',
  section_id: '',
  description: '',
  status: 'active',
};

export function CommunicationGroupsPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useCommunicationAccess();
  const { groups, groupsPagination, referenceData, loading, saving, error } = useAppSelector((state) => state.communication);
  const [form, setForm] = useState(initialForm);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const [selectedGroupId, setSelectedGroupId] = useState(null);
  const [memberForm, setMemberForm] = useState({ member_type: 'student', member_id: '' });

  useEffect(() => {
    dispatch(fetchCommunicationReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchGroups({ page, per_page: 10, search, status: statusFilter || undefined }));
  }, [dispatch, page, search, statusFilter]);

  const selectedGroup = useMemo(
    () => groups.find((item) => item.id === selectedGroupId) || null,
    [groups, selectedGroupId],
  );

  const memberOptions = useMemo(() => {
    if (memberForm.member_type === 'student') {
      return referenceData.students;
    }
    if (memberForm.member_type === 'guardian') {
      return referenceData.guardians;
    }
    return referenceData.staffMembers;
  }, [memberForm.member_type, referenceData]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      class_id: form.class_id || null,
      section_id: form.section_id || null,
    };
    const action = form.id ? updateGroup({ id: form.id, payload }) : createGroup(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setDrawerOpen(false);
      setForm(initialForm);
    }
  }

  return (
    <CommunicationPageShell
      title="Communication Groups"
      description="Build reusable audience pools for class, section, staff, and custom outreach without repeating recipient selection every time."
      actions={canManage ? <Button variant="contained" onClick={() => { setForm(initialForm); setDrawerOpen(true); }}>New Group</Button> : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 7 }}>
          <AppDataTable
            title="Groups"
            columns={[
              { key: 'name', header: 'Name' },
              { key: 'code', header: 'Code' },
              { key: 'group_type', header: 'Type' },
              { key: 'status', header: 'Status', render: (row) => <CommunicationStatusBadge value={row.status} /> },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <Stack direction="row" spacing={1}>
                    <Button size="small" onClick={() => setSelectedGroupId(row.id)}>Members</Button>
                    {canManage ? (
                      <>
                        <Button size="small" onClick={() => {
                          setForm({
                            ...initialForm,
                            ...row,
                            class_id: row.class_id || '',
                            section_id: row.section_id || '',
                          });
                          setDrawerOpen(true);
                        }}>
                          Edit
                        </Button>
                        <Button size="small" color="error" onClick={() => dispatch(deleteGroup(row.id))}>Delete</Button>
                      </>
                    ) : null}
                  </Stack>
                ),
              },
            ]}
            rows={groups}
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
                options: [{ value: '', label: 'All' }, ...statusOptions.group],
              },
            ]}
            pagination={{ page, totalPages: groupsPagination.totalPages, onPageChange: setPage }}
            emptyState="No communication groups found."
          />
        </Grid>

        <Grid size={{ xs: 12, lg: 5 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            {selectedGroup ? (
              <Stack spacing={2}>
                <Typography variant="h6">{selectedGroup.name}</Typography>
                <Typography variant="body2" color="text.secondary">{selectedGroup.description || 'No description added.'}</Typography>
                <Typography variant="subtitle2">Members</Typography>
                {(selectedGroup.members || []).length ? selectedGroup.members.map((member) => (
                  <Stack key={member.id} direction="row" justifyContent="space-between" alignItems="center">
                    <Typography variant="body2">{member.member_type} #{member.member_id}</Typography>
                    {canManage ? <Button size="small" color="error" onClick={() => dispatch(removeGroupMember({ id: selectedGroup.id, memberId: member.id }))}>Remove</Button> : null}
                  </Stack>
                )) : (
                  <Typography variant="body2" color="text.secondary">No members yet.</Typography>
                )}

                {canManage ? (
                  <Grid container spacing={2}>
                    <Grid size={{ xs: 12, md: 4 }}>
                      <TextField select fullWidth label="Member Type" value={memberForm.member_type} onChange={(event) => setMemberForm((current) => ({ ...current, member_type: event.target.value, member_id: '' }))}>
                        {participantTypeOptions.filter((option) => option.value !== 'user').map((option) => (
                          <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                        ))}
                      </TextField>
                    </Grid>
                    <Grid size={{ xs: 12, md: 5 }}>
                      <TextField select fullWidth label="Member" value={memberForm.member_id} onChange={(event) => setMemberForm((current) => ({ ...current, member_id: event.target.value }))}>
                        <MenuItem value="">Select member</MenuItem>
                        {memberOptions.map((item) => (
                          <MenuItem key={item.id} value={item.id}>{item.full_name || item.name || `#${item.id}`}</MenuItem>
                        ))}
                      </TextField>
                    </Grid>
                    <Grid size={{ xs: 12, md: 3 }}>
                      <Button fullWidth variant="contained" sx={{ height: '100%' }} onClick={() => dispatch(addGroupMember({ id: selectedGroup.id, payload: memberForm }))}>
                        Add
                      </Button>
                    </Grid>
                  </Grid>
                ) : null}
              </Stack>
            ) : (
              <Typography color="text.secondary">Select a group to view and manage its members.</Typography>
            )}
          </Paper>
        </Grid>
      </Grid>

      <CommunicationFormDrawer
        open={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        title={form.id ? 'Edit Group' : 'Create Group'}
        description="Create reusable communication groups for class-wise, section-wise, staff, or custom recipient collections."
        form={form}
        setForm={setForm}
        onSubmit={handleSubmit}
        submitLabel={form.id ? 'Update Group' : 'Save Group'}
        saving={saving}
        fields={[
          { key: 'name', label: 'Name' },
          { key: 'code', label: 'Code' },
          { key: 'group_type', label: 'Group Type', type: 'select', options: groupTypeOptions },
          { key: 'description', label: 'Description', type: 'multiline', rows: 4 },
          { key: 'status', label: 'Status', type: 'select', options: statusOptions.group },
        ]}
        extraContent={(
          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField select fullWidth label="Class" value={form.class_id} onChange={(event) => setForm((current) => ({ ...current, class_id: event.target.value, section_id: '' }))}>
                <MenuItem value="">Optional</MenuItem>
                {referenceData.classes.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField select fullWidth label="Section" value={form.section_id} onChange={(event) => setForm((current) => ({ ...current, section_id: event.target.value }))}>
                <MenuItem value="">Optional</MenuItem>
                {referenceData.sections
                  .filter((item) => !form.class_id || String(item.class_id) === String(form.class_id))
                  .map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
            </Grid>
          </Grid>
        )}
      />
    </CommunicationPageShell>
  );
}
