import AddCircleOutlineOutlinedIcon from '@mui/icons-material/AddCircleOutlineOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Divider, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { addGradeScale, createGradingSystem, deleteGradeScale, deleteGradingSystem, fetchGradingSystems, updateGradingSystem } from '../store/examinationSlice';

const blankScale = {
  grade_label: '',
  min_percentage: '',
  max_percentage: '',
  grade_point: '',
  remarks: '',
};

const initialForm = {
  id: null,
  name: '',
  code: '',
  grading_type: 'percentage',
  pass_percentage: '',
  description: '',
  status: 'active',
};

export function GradingSystemsPage() {
  const dispatch = useAppDispatch();
  const { gradingSystems, loading, saving, error } = useAppSelector((state) => state.examination);
  const canManage = useAppSelector((state) => state.auth.user?.permissions?.includes('exams.manage'));
  const [form, setForm] = useState(initialForm);
  const [scaleForm, setScaleForm] = useState(blankScale);
  const [selectedSystemId, setSelectedSystemId] = useState('');
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchGradingSystems());
  }, [dispatch]);

  const rows = useMemo(() => {
    const query = search.trim().toLowerCase();
    return gradingSystems.filter((item) => !query || `${item.name} ${item.code}`.toLowerCase().includes(query));
  }, [gradingSystems, search]);

  const selectedSystem = useMemo(
    () => gradingSystems.find((item) => String(item.id) === String(selectedSystemId)),
    [gradingSystems, selectedSystemId],
  );

  async function handleSystemSubmit(event) {
    event.preventDefault();
    if (!canManage) return;

    const payload = {
      ...form,
      pass_percentage: form.pass_percentage === '' ? null : Number(form.pass_percentage),
    };

    const action = form.id
      ? updateGradingSystem({ id: form.id, payload: { ...payload, id: undefined } })
      : createGradingSystem(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchGradingSystems());
    }
  }

  async function handleScaleSubmit(event) {
    event.preventDefault();
    if (!canManage || !selectedSystemId) return;

    const result = await dispatch(addGradeScale({
      gradingSystemId: Number(selectedSystemId),
      payload: {
        grading_system_id: Number(selectedSystemId),
        grade_label: scaleForm.grade_label,
        min_percentage: Number(scaleForm.min_percentage),
        max_percentage: Number(scaleForm.max_percentage),
        grade_point: scaleForm.grade_point === '' ? null : Number(scaleForm.grade_point),
        remarks: scaleForm.remarks || null,
      },
    }));

    if (!result.error) {
      setScaleForm(blankScale);
    }
  }

  return (
    <ExaminationPageShell
      title="Grading Systems"
      description="Maintain grading schemes and detailed scale bands that result processing can map into grades and GPA values."
    >
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleSystemSubmit}>
              <Typography variant="h6">{form.id ? 'Edit Grading System' : 'Create Grading System'}</Typography>
              {error ? <Alert severity="error">{error}</Alert> : null}
              <TextField label="Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} />
              <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value }))} />
              <TextField select label="Grading Type" value={form.grading_type} onChange={(event) => setForm((current) => ({ ...current, grading_type: event.target.value }))}>
                <MenuItem value="percentage">Percentage</MenuItem>
                <MenuItem value="grade">Grade</MenuItem>
                <MenuItem value="gpa">GPA</MenuItem>
              </TextField>
              <TextField type="number" label="Pass Percentage" value={form.pass_percentage} onChange={(event) => setForm((current) => ({ ...current, pass_percentage: event.target.value }))} />
              <TextField label="Description" multiline minRows={3} value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />
              <TextField select label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                <MenuItem value="active">Active</MenuItem>
                <MenuItem value="inactive">Inactive</MenuItem>
              </TextField>
              <Button type="submit" variant="contained" disabled={saving || !canManage}>
                {saving ? 'Saving...' : form.id ? 'Update Grading System' : 'Create Grading System'}
              </Button>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Grading Systems"
            columns={[
              { key: 'name', header: 'Name' },
              { key: 'code', header: 'Code' },
              { key: 'grading_type', header: 'Type' },
              { key: 'pass_percentage', header: 'Pass %' },
              { key: 'status', header: 'Status' },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => canManage ? (
                  <Stack direction="row" spacing={1}>
                    <IconButton color="primary" onClick={() => {
                      setForm({
                        id: row.id,
                        name: row.name || '',
                        code: row.code || '',
                        grading_type: row.grading_type || 'percentage',
                        pass_percentage: row.pass_percentage ?? '',
                        description: row.description || '',
                        status: row.status || 'active',
                      });
                      setSelectedSystemId(row.id);
                    }}>
                      <EditOutlinedIcon />
                    </IconButton>
                    <IconButton color="error" onClick={() => dispatch(deleteGradingSystem(row.id))}>
                      <DeleteOutlineOutlinedIcon />
                    </IconButton>
                  </Stack>
                ) : 'View only',
              },
            ]}
            rows={rows}
            loading={loading}
            searchValue={search}
            onSearchChange={setSearch}
            emptyState="No grading systems created yet."
          />

          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)', mt: 3 }}>
            <Stack spacing={2} component="form" onSubmit={handleScaleSubmit}>
              <Typography variant="h6">Grade Scales</Typography>
              <TextField select label="Grading System" value={selectedSystemId} onChange={(event) => setSelectedSystemId(event.target.value)}>
                <MenuItem value="">Select</MenuItem>
                {gradingSystems.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 4 }}>
                  <TextField fullWidth label="Grade Label" value={scaleForm.grade_label} onChange={(event) => setScaleForm((current) => ({ ...current, grade_label: event.target.value }))} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <TextField fullWidth type="number" label="Min %" value={scaleForm.min_percentage} onChange={(event) => setScaleForm((current) => ({ ...current, min_percentage: event.target.value }))} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <TextField fullWidth type="number" label="Max %" value={scaleForm.max_percentage} onChange={(event) => setScaleForm((current) => ({ ...current, max_percentage: event.target.value }))} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <TextField fullWidth type="number" label="Grade Point" value={scaleForm.grade_point} onChange={(event) => setScaleForm((current) => ({ ...current, grade_point: event.target.value }))} />
                </Grid>
                <Grid size={{ xs: 12, md: 8 }}>
                  <TextField fullWidth label="Remarks" value={scaleForm.remarks} onChange={(event) => setScaleForm((current) => ({ ...current, remarks: event.target.value }))} />
                </Grid>
              </Grid>
              <Button type="submit" variant="outlined" startIcon={<AddCircleOutlineOutlinedIcon />} disabled={!selectedSystemId || saving || !canManage}>
                Add Grade Scale
              </Button>

              <Divider />

              {(selectedSystem?.grade_scales || []).length ? (
                (selectedSystem.grade_scales || []).map((scale) => (
                  <Stack key={scale.id} direction="row" justifyContent="space-between" alignItems="center" sx={{ py: 1 }}>
                    <Typography variant="body2">
                      {scale.grade_label}: {scale.min_percentage}% - {scale.max_percentage}% {scale.grade_point != null ? `• GPA ${scale.grade_point}` : ''}
                    </Typography>
                    {canManage ? (
                      <IconButton color="error" onClick={() => dispatch(deleteGradeScale({ id: scale.id, gradingSystemId: selectedSystem.id }))}>
                        <DeleteOutlineOutlinedIcon />
                      </IconButton>
                    ) : null}
                  </Stack>
                ))
              ) : (
                <Typography variant="body2" color="text.secondary">
                  Select a grading system to view or add its scale bands.
                </Typography>
              )}
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </ExaminationPageShell>
  );
}
