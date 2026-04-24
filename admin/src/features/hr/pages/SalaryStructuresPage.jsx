import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createHrResource, deleteHrResource, fetchHrOptions, fetchHrResource, updateHrResource } from '../store/hrSlice';

const initialItem = { salary_component_id: '', amount: '', percentage: '', component_type: 'earning' };
const initialForm = {
  id: null,
  staff_id: '',
  effective_from: '',
  effective_to: '',
  basic_salary: '',
  status: 'active',
  items: [initialItem],
};

export function SalaryStructuresPage() {
  const dispatch = useAppDispatch();
  const structureState = useAppSelector((state) => state.hr.resources.salaryStructures);
  const options = useAppSelector((state) => state.hr.options);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchHrOptions());
    dispatch(fetchHrResource({ resource: 'salaryStructures', params: {} }));
  }, [dispatch]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      basic_salary: Number(form.basic_salary),
      effective_to: form.effective_to || null,
      items: form.items.map((item) => ({
        ...item,
        amount: item.amount === '' ? null : Number(item.amount),
        percentage: item.percentage === '' ? null : Number(item.percentage),
      })),
    };
    const action = form.id
      ? updateHrResource({ resource: 'salaryStructures', id: form.id, payload })
      : createHrResource({ resource: 'salaryStructures', payload });
    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchHrResource({ resource: 'salaryStructures', params: {} }));
    }
  }

  return (
    <Stack spacing={3}>
      <PermissionGate permission="hr.manage">
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h5">Salary Structure Builder</Typography>
            <Typography variant="body2" color="text.secondary">
              Compose earnings and deductions per staff member so payroll runs can generate accurate payslips.
            </Typography>
            {structureState.error ? <Alert severity="error">{structureState.error}</Alert> : null}
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField select fullWidth label="Staff" value={form.staff_id} onChange={(event) => setForm((current) => ({ ...current, staff_id: event.target.value }))}>
                  <MenuItem value="">Select</MenuItem>
                  {(options.staff || []).map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
                </TextField>
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth type="date" label="Effective From" value={form.effective_from} onChange={(event) => setForm((current) => ({ ...current, effective_from: event.target.value }))} InputLabelProps={{ shrink: true }} />
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth type="date" label="Effective To" value={form.effective_to} onChange={(event) => setForm((current) => ({ ...current, effective_to: event.target.value }))} InputLabelProps={{ shrink: true }} />
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField fullWidth label="Basic Salary" value={form.basic_salary} onChange={(event) => setForm((current) => ({ ...current, basic_salary: event.target.value }))} />
              </Grid>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField select fullWidth label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                  <MenuItem value="active">active</MenuItem>
                  <MenuItem value="inactive">inactive</MenuItem>
                </TextField>
              </Grid>
            </Grid>

            <Typography variant="subtitle1">Structure Items</Typography>
            {form.items.map((item, index) => (
              <Grid container spacing={2} key={`salary-item-${index}`}>
                <Grid size={{ xs: 12, md: 4 }}>
                  <TextField select fullWidth label="Salary Component" value={item.salary_component_id} onChange={(event) => setForm((current) => ({
                    ...current,
                    items: current.items.map((entry, entryIndex) => entryIndex === index ? { ...entry, salary_component_id: event.target.value } : entry),
                  }))}>
                    <MenuItem value="">Select</MenuItem>
                    {(options.salaryComponents || []).map((component) => <MenuItem key={component.id} value={component.id}>{component.name}</MenuItem>)}
                  </TextField>
                </Grid>
                <Grid size={{ xs: 12, md: 3 }}>
                  <TextField fullWidth label="Amount" value={item.amount} onChange={(event) => setForm((current) => ({
                    ...current,
                    items: current.items.map((entry, entryIndex) => entryIndex === index ? { ...entry, amount: event.target.value } : entry),
                  }))} />
                </Grid>
                <Grid size={{ xs: 12, md: 3 }}>
                  <TextField fullWidth label="Percentage" value={item.percentage} onChange={(event) => setForm((current) => ({
                    ...current,
                    items: current.items.map((entry, entryIndex) => entryIndex === index ? { ...entry, percentage: event.target.value } : entry),
                  }))} />
                </Grid>
                <Grid size={{ xs: 12, md: 2 }}>
                  <TextField select fullWidth label="Type" value={item.component_type} onChange={(event) => setForm((current) => ({
                    ...current,
                    items: current.items.map((entry, entryIndex) => entryIndex === index ? { ...entry, component_type: event.target.value } : entry),
                  }))}>
                    <MenuItem value="earning">earning</MenuItem>
                    <MenuItem value="deduction">deduction</MenuItem>
                  </TextField>
                </Grid>
              </Grid>
            ))}
            <Stack direction="row" spacing={2}>
              <Button variant="outlined" onClick={() => setForm((current) => ({ ...current, items: [...current.items, initialItem] }))}>Add Item</Button>
              <Button type="submit" variant="contained" disabled={structureState.saving}>{structureState.saving ? 'Saving...' : form.id ? 'Update Structure' : 'Create Structure'}</Button>
            </Stack>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Salary Structures"
        columns={[
          { key: 'staff', header: 'Staff', render: (row) => row.staff?.full_name || row.staff_id },
          { key: 'effective_from', header: 'Effective From' },
          { key: 'effective_to', header: 'Effective To' },
          { key: 'basic_salary', header: 'Basic' },
          { key: 'gross_salary', header: 'Gross' },
          { key: 'net_salary', header: 'Net' },
          { key: 'status', header: 'Status' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <PermissionGate permission="hr.manage" fallback={null}>
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm({
                    id: row.id,
                    staff_id: row.staff?.id || row.staff_id || '',
                    effective_from: row.effective_from || '',
                    effective_to: row.effective_to || '',
                    basic_salary: row.basic_salary || '',
                    status: row.status || 'active',
                    items: (row.items || []).map((item) => ({
                      salary_component_id: item.salary_component_id,
                      amount: item.amount || '',
                      percentage: item.percentage || '',
                      component_type: item.salary_component?.component_type || 'earning',
                    })),
                  })}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => dispatch(deleteHrResource({ resource: 'salaryStructures', id: row.id }))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              </PermissionGate>
            ),
          },
        ]}
        rows={structureState.items}
        loading={structureState.loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No salary structures created yet."
      />
    </Stack>
  );
}
