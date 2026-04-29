import PlayArrowOutlinedIcon from '@mui/icons-material/PlayArrowOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ReportsMiniBarChart } from '../components/ReportsMiniBarChart';
import { ReportsPageShell } from '../components/ReportsPageShell';
import { useReportsAccess } from '../hooks/useReportsAccess';
import { fetchReportDefinitions, fetchReportsReferenceData, runReport } from '../store/reportsSlice';
import { fileTypeOptions } from '../types/options';

export function RunReportPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { canRun } = useReportsAccess();
  const { definitions, referenceData, saving, error } = useAppSelector((state) => state.reports);
  const [definitionId, setDefinitionId] = useState('');
  const [fileType, setFileType] = useState('json');
  const [filters, setFilters] = useState({
    academic_year_id: '',
    class_id: '',
    section_id: '',
    date_from: '',
    date_to: '',
  });

  useEffect(() => {
    dispatch(fetchReportDefinitions({ per_page: 100 }));
    dispatch(fetchReportsReferenceData());
  }, [dispatch]);

  const selectedDefinition = useMemo(
    () => definitions.find((item) => String(item.id) === String(definitionId)),
    [definitions, definitionId],
  );

  async function handleRun() {
    const action = await dispatch(runReport({
      report_definition_id: Number(definitionId),
      file_type: fileType,
      parameters: Object.fromEntries(
        Object.entries(filters).filter(([, value]) => value !== ''),
      ),
    }));

    if (action.payload?.id) {
      navigate('/reports/results');
    }
  }

  return (
    <ReportsPageShell
      title="Run Report"
      description="Launch an on-demand report with filters and export format in one quick flow."
      actions={canRun ? (
        <Button variant="contained" startIcon={<PlayArrowOutlinedIcon />} disabled={!definitionId || saving} onClick={handleRun}>
          {saving ? 'Running...' : 'Run Report'}
        </Button>
      ) : null}
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Typography variant="h6">Execution Setup</Typography>
          {error ? <Alert severity="error">{error}</Alert> : null}
          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField select fullWidth label="Report Definition" value={definitionId} onChange={(event) => setDefinitionId(event.target.value)}>
                <MenuItem value="">Select</MenuItem>
                {definitions.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField select fullWidth label="Export Format" value={fileType} onChange={(event) => setFileType(event.target.value)}>
                {fileTypeOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField select fullWidth label="Academic Year" value={filters.academic_year_id} onChange={(event) => setFilters((current) => ({ ...current, academic_year_id: event.target.value }))}>
                <MenuItem value="">All</MenuItem>
                {referenceData.academicYears.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField select fullWidth label="Class" value={filters.class_id} onChange={(event) => setFilters((current) => ({ ...current, class_id: event.target.value }))}>
                <MenuItem value="">All</MenuItem>
                {referenceData.classes.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField select fullWidth label="Section" value={filters.section_id} onChange={(event) => setFilters((current) => ({ ...current, section_id: event.target.value }))}>
                <MenuItem value="">All</MenuItem>
                {referenceData.sections.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField fullWidth label="From Date" type="date" InputLabelProps={{ shrink: true }} value={filters.date_from} onChange={(event) => setFilters((current) => ({ ...current, date_from: event.target.value }))} />
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField fullWidth label="To Date" type="date" InputLabelProps={{ shrink: true }} value={filters.date_to} onChange={(event) => setFilters((current) => ({ ...current, date_to: event.target.value }))} />
            </Grid>
          </Grid>
        </Stack>
      </Paper>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <ReportsMiniBarChart
            title="Selected Report Snapshot"
            subtitle="A quick way to sanity-check the chosen definition before running it."
            items={selectedDefinition ? [
              { label: 'Schedules', value: selectedDefinition.schedules_count || 0 },
              { label: 'Runs', value: selectedDefinition.runs_count || 0 },
              { label: 'Status', value: selectedDefinition.status === 'active' ? 1 : 0 },
            ] : []}
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1}>
              <Typography variant="h6">Definition Notes</Typography>
              <Typography variant="body2" color="text.secondary">
                {selectedDefinition?.description || 'Select a report definition to preview its scope and saved filter intent.'}
              </Typography>
              {selectedDefinition ? (
                <>
                  <Typography variant="caption" color="text.secondary">Module: {selectedDefinition.module}</Typography>
                  <Typography variant="caption" color="text.secondary">Code: {selectedDefinition.code}</Typography>
                </>
              ) : null}
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </ReportsPageShell>
  );
}
