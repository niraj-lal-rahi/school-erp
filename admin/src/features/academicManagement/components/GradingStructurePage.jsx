import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Button, IconButton, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createAcademicResource, deleteAcademicResource, fetchAcademicOptions, fetchAcademicResource, updateAcademicResource } from '../store/academicManagementSlice';

const blankScaleItem = { grade_label: '', min_percentage: '', max_percentage: '', grade_point: '', remarks: '' };

export function GradingStructurePage() {
  const dispatch = useAppDispatch();
  const { items, loading, saving, error } = useAppSelector((state) => state.academicManagement.resources.gradingStructures);
  const options = useAppSelector((state) => state.academicManagement.options);
  const [form, setForm] = useState({ academic_year_id: '', name: '', description: '', pass_percentage: '', status: 'active', scale_items: [{ ...blankScaleItem }] });

  useEffect(() => {
    dispatch(fetchAcademicOptions());
    dispatch(fetchAcademicResource({ resource: 'gradingStructures', params: {} }));
  }, [dispatch]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      pass_percentage: form.pass_percentage ? Number(form.pass_percentage) : null,
      scale_items: form.scale_items.map((item) => ({
        ...item,
        min_percentage: Number(item.min_percentage),
        max_percentage: Number(item.max_percentage),
        grade_point: item.grade_point === '' ? null : Number(item.grade_point),
      })),
    };

    const action = form.id
      ? updateAcademicResource({ resource: 'gradingStructures', id: form.id, payload })
      : createAcademicResource({ resource: 'gradingStructures', payload });

    const result = await dispatch(action);
    if (!result.error) {
      setForm({ academic_year_id: '', name: '', description: '', pass_percentage: '', status: 'active', scale_items: [{ ...blankScaleItem }] });
    }
  }

  return (
    <Stack spacing={3}>
      <PermissionGate permission="academic-management.manage">
        <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack spacing={2}>
            <Typography variant="h5">Grading Structure</Typography>
            {error ? <Typography color="error">{error}</Typography> : null}
            <TextField select label="Academic Year" value={form.academic_year_id} onChange={(e) => setForm((current) => ({ ...current, academic_year_id: e.target.value }))}>
              {options.academicYears.map((year) => <option key={year.id} value={year.id}>{year.name}</option>)}
            </TextField>
            <TextField label="Name" value={form.name} onChange={(e) => setForm((current) => ({ ...current, name: e.target.value }))} />
            <TextField label="Description" value={form.description} onChange={(e) => setForm((current) => ({ ...current, description: e.target.value }))} />
            <TextField label="Pass Percentage" type="number" value={form.pass_percentage} onChange={(e) => setForm((current) => ({ ...current, pass_percentage: e.target.value }))} />
            <TextField select label="Status" value={form.status} onChange={(e) => setForm((current) => ({ ...current, status: e.target.value }))}>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </TextField>
            {form.scale_items.map((item, index) => (
              <Stack key={index} direction={{ xs: 'column', md: 'row' }} spacing={1}>
                <TextField label="Grade" value={item.grade_label} onChange={(e) => setForm((current) => ({ ...current, scale_items: current.scale_items.map((scaleItem, scaleIndex) => scaleIndex === index ? { ...scaleItem, grade_label: e.target.value } : scaleItem) }))} />
                <TextField label="Min %" type="number" value={item.min_percentage} onChange={(e) => setForm((current) => ({ ...current, scale_items: current.scale_items.map((scaleItem, scaleIndex) => scaleIndex === index ? { ...scaleItem, min_percentage: e.target.value } : scaleItem) }))} />
                <TextField label="Max %" type="number" value={item.max_percentage} onChange={(e) => setForm((current) => ({ ...current, scale_items: current.scale_items.map((scaleItem, scaleIndex) => scaleIndex === index ? { ...scaleItem, max_percentage: e.target.value } : scaleItem) }))} />
                <TextField label="Grade Point" type="number" value={item.grade_point} onChange={(e) => setForm((current) => ({ ...current, scale_items: current.scale_items.map((scaleItem, scaleIndex) => scaleIndex === index ? { ...scaleItem, grade_point: e.target.value } : scaleItem) }))} />
                <TextField label="Remarks" value={item.remarks} onChange={(e) => setForm((current) => ({ ...current, scale_items: current.scale_items.map((scaleItem, scaleIndex) => scaleIndex === index ? { ...scaleItem, remarks: e.target.value } : scaleItem) }))} />
              </Stack>
            ))}
            <Button onClick={() => setForm((current) => ({ ...current, scale_items: [...current.scale_items, { ...blankScaleItem }] }))}>Add Grade Scale</Button>
            <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : 'Save Grading Structure'}</Button>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Grading Structures"
        columns={[
          { key: 'name', header: 'Name' },
          { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || row.academic_year_id },
          { key: 'pass_percentage', header: 'Pass %' },
          { key: 'status', header: 'Status' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <IconButton color="primary" onClick={() => setForm({ ...row, scale_items: row.scale_items || [{ ...blankScaleItem }] })}>
                  <EditOutlinedIcon />
                </IconButton>
                <IconButton color="error" onClick={() => dispatch(deleteAcademicResource({ resource: 'gradingStructures', id: row.id }))}>
                  <DeleteOutlineOutlinedIcon />
                </IconButton>
              </Stack>
            ),
          },
        ]}
        rows={items}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
      />
    </Stack>
  );
}
