import BuildOutlinedIcon from '@mui/icons-material/BuildOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ReportsPageShell } from '../components/ReportsPageShell';
import { createReportDefinition, fetchReportsReferenceData } from '../store/reportsSlice';

const starterQueryConfig = '{\n  "table": "student_results",\n  "fields": ["student_id", "percentage", "result_status"],\n  "filters": {\n    "academic_year_id": null,\n    "class_id": null,\n    "section_id": null\n  },\n  "group_by": ["result_status"],\n  "metrics": ["count"]\n}';

export function CustomReportBuilderPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { referenceData, saving, error } = useAppSelector((state) => state.reports);
  const [form, setForm] = useState({
    name: '',
    code: '',
    description: '',
    academic_year_id: '',
    class_id: '',
    section_id: '',
    query_config: starterQueryConfig,
  });

  useEffect(() => {
    dispatch(fetchReportsReferenceData());
  }, [dispatch]);

  const jsonError = useMemo(() => {
    try {
      JSON.parse(form.query_config);
      return null;
    } catch {
      return 'The custom query config must be valid JSON.';
    }
  }, [form.query_config]);

  async function handleSubmit(event) {
    event.preventDefault();
    if (jsonError) {
      return;
    }

    const action = await dispatch(createReportDefinition({
      name: form.name,
      code: form.code,
      module: 'custom',
      description: form.description || null,
      query_config: JSON.parse(form.query_config),
      default_filters: {
        academic_year_id: form.academic_year_id || undefined,
        class_id: form.class_id || undefined,
        section_id: form.section_id || undefined,
      },
      is_system: false,
      status: 'active',
    }));

    if (action.payload?.id) {
      navigate('/reports/saved');
    }
  }

  return (
    <ReportsPageShell
      title="Custom Report Builder"
      description="Create a basic custom report definition by combining safe filters with a whitelisted query configuration."
      actions={(
        <Button variant="contained" startIcon={<BuildOutlinedIcon />} disabled={saving || Boolean(jsonError)} onClick={handleSubmit}>
          {saving ? 'Saving...' : 'Save Custom Report'}
        </Button>
      )}
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack component="form" spacing={2} onSubmit={handleSubmit}>
          {error ? <Alert severity="error">{error}</Alert> : null}
          {jsonError ? <Alert severity="warning">{jsonError}</Alert> : null}
          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField fullWidth label="Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} required />
            </Grid>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField fullWidth label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value.toUpperCase().replace(/\s+/g, '-') }))} required />
            </Grid>
            <Grid size={{ xs: 12 }}>
              <TextField fullWidth multiline minRows={2} label="Description" value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />
            </Grid>
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField select fullWidth label="Academic Year" value={form.academic_year_id} onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value }))}>
                <MenuItem value="">All</MenuItem>
                {referenceData.academicYears.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField select fullWidth label="Class" value={form.class_id} onChange={(event) => setForm((current) => ({ ...current, class_id: event.target.value }))}>
                <MenuItem value="">All</MenuItem>
                {referenceData.classes.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField select fullWidth label="Section" value={form.section_id} onChange={(event) => setForm((current) => ({ ...current, section_id: event.target.value }))}>
                <MenuItem value="">All</MenuItem>
                {referenceData.sections.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12 }}>
              <TextField fullWidth multiline minRows={16} label="Query Config (JSON)" value={form.query_config} onChange={(event) => setForm((current) => ({ ...current, query_config: event.target.value }))} />
            </Grid>
          </Grid>
          <Typography variant="body2" color="text.secondary">
            This builder is intentionally basic: it helps your team create safe reusable definitions without hand-editing backend seed data.
          </Typography>
        </Stack>
      </Paper>
    </ReportsPageShell>
  );
}
