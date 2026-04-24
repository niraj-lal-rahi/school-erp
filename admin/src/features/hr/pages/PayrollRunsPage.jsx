import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import PlayCircleOutlineOutlinedIcon from '@mui/icons-material/PlayCircleOutlineOutlined';
import PriceCheckOutlinedIcon from '@mui/icons-material/PriceCheckOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createHrResource,
  fetchHrResource,
  runPayrollWorkflowAction,
  setHrResourceFilters,
  setHrResourcePage,
} from '../store/hrSlice';

const initialForm = {
  payroll_month: '',
  payroll_year: '',
  status: 'draft',
};

export function PayrollRunsPage() {
  const dispatch = useAppDispatch();
  const runState = useAppSelector((state) => state.hr.resources.payrollRuns);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchHrResource({
      resource: 'payrollRuns',
      params: {
        ...runState.filters,
      },
    }));
  }, [dispatch, runState.filters]);

  async function handleCreate(event) {
    event.preventDefault();
    const result = await dispatch(createHrResource({
      resource: 'payrollRuns',
      payload: {
        payroll_month: Number(form.payroll_month),
        payroll_year: Number(form.payroll_year),
        status: form.status,
      },
    }));

    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchHrResource({ resource: 'payrollRuns', params: runState.filters }));
    }
  }

  async function handleWorkflow(payrollRunId, action) {
    const result = await dispatch(runPayrollWorkflowAction({ payrollRunId, action }));
    if (!result.error) {
      dispatch(fetchHrResource({ resource: 'payrollRuns', params: runState.filters }));
    }
  }

  return (
    <Stack spacing={3}>
      <PermissionGate permission="hr.manage">
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleCreate}>
            <Typography variant="h5">Payroll Runs</Typography>
            <Typography variant="body2" color="text.secondary">
              Create payroll cycles, process them into payslips, finalize totals, and then mark them paid.
            </Typography>
            {runState.error ? <Alert severity="error">{runState.error}</Alert> : null}
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth select label="Payroll Month" value={form.payroll_month} onChange={(event) => setForm((current) => ({ ...current, payroll_month: event.target.value }))}>
                  <MenuItem value="">Select</MenuItem>
                  {Array.from({ length: 12 }, (_, index) => index + 1).map((month) => (
                    <MenuItem key={month} value={month}>{month}</MenuItem>
                  ))}
                </TextField>
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth label="Payroll Year" value={form.payroll_year} onChange={(event) => setForm((current) => ({ ...current, payroll_year: event.target.value }))} />
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth select label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                  {['draft', 'processing', 'finalized', 'paid', 'cancelled'].map((status) => (
                    <MenuItem key={status} value={status}>{status}</MenuItem>
                  ))}
                </TextField>
              </Grid>
            </Grid>
            <Button type="submit" variant="contained" disabled={runState.saving}>{runState.saving ? 'Saving...' : 'Create Payroll Run'}</Button>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Payroll Runs"
        columns={[
          { key: 'period', header: 'Period', render: (row) => `${row.payroll_month}/${row.payroll_year}` },
          { key: 'status', header: 'Status' },
          { key: 'total_gross', header: 'Gross' },
          { key: 'total_deductions', header: 'Deductions' },
          { key: 'total_net', header: 'Net' },
          {
            key: 'actions',
            header: 'Workflow',
            render: (row) => (
              <PermissionGate permission="hr.manage" fallback={null}>
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => handleWorkflow(row.id, 'process')}>
                    <PlayCircleOutlineOutlinedIcon />
                  </IconButton>
                  <IconButton color="success" onClick={() => handleWorkflow(row.id, 'finalize')}>
                    <CheckCircleOutlineOutlinedIcon />
                  </IconButton>
                  <IconButton color="secondary" onClick={() => handleWorkflow(row.id, 'mark-paid')}>
                    <PriceCheckOutlinedIcon />
                  </IconButton>
                </Stack>
              </PermissionGate>
            ),
          },
        ]}
        rows={runState.items}
        loading={runState.loading}
        searchValue=""
        onSearchChange={() => {}}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: runState.filters.status || '',
            onChange: (value) => {
              dispatch(setHrResourceFilters({ resource: 'payrollRuns', filters: { status: value } }));
              dispatch(fetchHrResource({ resource: 'payrollRuns', params: { ...runState.filters, status: value } }));
            },
            options: [{ value: '', label: 'All' }, ...['draft', 'processing', 'finalized', 'paid', 'cancelled'].map((item) => ({ value: item, label: item }))],
          },
        ]}
        pagination={{
          page: runState.pagination.page,
          totalPages: runState.pagination.totalPages,
          onPageChange: (page) => dispatch(setHrResourcePage({ resource: 'payrollRuns', page })),
        }}
        emptyState="No payroll runs created yet."
      />
    </Stack>
  );
}
