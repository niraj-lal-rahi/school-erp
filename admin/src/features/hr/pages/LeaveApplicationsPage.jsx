import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import CloseOutlinedIcon from '@mui/icons-material/CloseOutlined';
import SendOutlinedIcon from '@mui/icons-material/SendOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createHrResource,
  fetchHrOptions,
  fetchHrResource,
  runLeaveWorkflowAction,
  setHrResourceFilters,
  setHrResourcePage,
} from '../store/hrSlice';

const initialForm = {
  staff_id: '',
  leave_type_id: '',
  start_date: '',
  end_date: '',
  reason: '',
  status: 'draft',
};

export function LeaveApplicationsPage() {
  const dispatch = useAppDispatch();
  const leaveState = useAppSelector((state) => state.hr.resources.leaveApplications);
  const options = useAppSelector((state) => state.hr.options);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchHrOptions());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchHrResource({
      resource: 'leaveApplications',
      params: {
        per_page: 10,
        page: leaveState.pagination.page,
        ...Object.fromEntries(Object.entries(leaveState.filters || {}).filter(([, value]) => value !== '' && value !== undefined)),
      },
    }));
  }, [dispatch, leaveState.filters, leaveState.pagination.page]);

  async function handleCreate(event) {
    event.preventDefault();
    const result = await dispatch(createHrResource({ resource: 'leaveApplications', payload: form }));
    if (!result.error) {
      setForm(initialForm);
    }
  }

  async function runAction(leaveApplicationId, action) {
    const payload = action === 'submit' ? {} : { review_remarks: `${action} via admin panel.` };
    const result = await dispatch(runLeaveWorkflowAction({ leaveApplicationId, action, payload }));
    if (!result.error) {
      dispatch(fetchHrResource({
        resource: 'leaveApplications',
        params: {
          per_page: 10,
          page: leaveState.pagination.page,
          ...Object.fromEntries(Object.entries(leaveState.filters || {}).filter(([, value]) => value !== '' && value !== undefined)),
        },
      }));
    }
  }

  return (
    <Stack spacing={3}>
      <PermissionGate permission="hr.manage">
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleCreate}>
            <Typography variant="h5">Leave Application Review</Typography>
            <Typography variant="body2" color="text.secondary">
              Create and review leave requests in one place. Approval here also updates leave balances and attendance on the backend.
            </Typography>
            {leaveState.error ? <Alert severity="error">{leaveState.error}</Alert> : null}
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField select fullWidth label="Staff" value={form.staff_id} onChange={(event) => setForm((current) => ({ ...current, staff_id: event.target.value }))}>
                  <MenuItem value="">Select</MenuItem>
                  {(options.staff || []).map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
                </TextField>
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField select fullWidth label="Leave Type" value={form.leave_type_id} onChange={(event) => setForm((current) => ({ ...current, leave_type_id: event.target.value }))}>
                  <MenuItem value="">Select</MenuItem>
                  {(options.leaveTypes || []).map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                </TextField>
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField select fullWidth label="Initial Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                  <MenuItem value="draft">draft</MenuItem>
                  <MenuItem value="submitted">submitted</MenuItem>
                </TextField>
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth type="date" label="Start Date" value={form.start_date} onChange={(event) => setForm((current) => ({ ...current, start_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth type="date" label="End Date" value={form.end_date} onChange={(event) => setForm((current) => ({ ...current, end_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth label="Reason" value={form.reason} onChange={(event) => setForm((current) => ({ ...current, reason: event.target.value }))} />
              </Grid>
            </Grid>
            <Button type="submit" variant="contained" disabled={leaveState.saving}>{leaveState.saving ? 'Saving...' : 'Create Application'}</Button>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Leave Applications"
        columns={[
          { key: 'staff', header: 'Staff', render: (row) => row.staff?.full_name || row.staff_id },
          { key: 'leave_type', header: 'Leave Type', render: (row) => row.leave_type?.name || row.leave_type_id },
          { key: 'period', header: 'Period', render: (row) => `${row.start_date} to ${row.end_date}` },
          { key: 'total_days', header: 'Days' },
          { key: 'status', header: 'Status' },
          {
            key: 'actions',
            header: 'Workflow',
            render: (row) => (
              <PermissionGate permission="hr.manage" fallback={null}>
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => runAction(row.id, 'submit')}><SendOutlinedIcon /></IconButton>
                  <IconButton color="success" onClick={() => runAction(row.id, 'approve')}><CheckCircleOutlineOutlinedIcon /></IconButton>
                  <IconButton color="warning" onClick={() => runAction(row.id, 'reject')}><CloseOutlinedIcon /></IconButton>
                  <Button size="small" onClick={() => runAction(row.id, 'cancel')}>Cancel</Button>
                </Stack>
              </PermissionGate>
            ),
          },
        ]}
        rows={leaveState.items}
        loading={leaveState.loading}
        searchValue={leaveState.filters.search || ''}
        onSearchChange={(value) => {
          dispatch(setHrResourceFilters({ resource: 'leaveApplications', filters: { search: value } }));
          dispatch(setHrResourcePage({ resource: 'leaveApplications', page: 1 }));
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: leaveState.filters.status || '',
            onChange: () => {},
            options: [{ value: '', label: 'All' }, ...['draft', 'submitted', 'approved', 'rejected', 'cancelled'].map((item) => ({ value: item, label: item }))],
          },
          {
            key: 'staff_id',
            label: 'Staff',
            value: leaveState.filters.staff_id || '',
            onChange: () => {},
            options: [{ value: '', label: 'All' }, ...(options.staff || []).map((item) => ({ value: item.id, label: item.full_name }))],
          },
          {
            key: 'leave_type_id',
            label: 'Leave Type',
            value: leaveState.filters.leave_type_id || '',
            onChange: () => {},
            options: [{ value: '', label: 'All' }, ...(options.leaveTypes || []).map((item) => ({ value: item.id, label: item.name }))],
          },
        ].map((filter) => ({
          ...filter,
          onChange: (value) => {
            dispatch(setHrResourceFilters({ resource: 'leaveApplications', filters: { [filter.key]: value } }));
            dispatch(setHrResourcePage({ resource: 'leaveApplications', page: 1 }));
          },
        }))}
        pagination={{
          page: leaveState.pagination.page,
          totalPages: leaveState.pagination.totalPages,
          onPageChange: (page) => dispatch(setHrResourcePage({ resource: 'leaveApplications', page })),
        }}
        emptyState="No leave applications found."
      />
    </Stack>
  );
}
