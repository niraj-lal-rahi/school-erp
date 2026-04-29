import AutoFixHighOutlinedIcon from '@mui/icons-material/AutoFixHighOutlined';
import PublishOutlinedIcon from '@mui/icons-material/PublishOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { PerformanceMetricCard } from '../components/PerformanceMetricCard';
import { ResultStatusChip } from '../components/ResultStatusChip';
import { computeResults, fetchExaminationMasterData, fetchResults, publishResults } from '../store/examinationSlice';

export function ResultProcessingPage() {
  const dispatch = useAppDispatch();
  const { exams, gradingSystems, results, loading, saving, error } = useAppSelector((state) => state.examination);
  const canManage = useAppSelector((state) => state.auth.user?.permissions?.includes('exams.manage'));
  const [examId, setExamId] = useState('');
  const [gradingSystemId, setGradingSystemId] = useState('');

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
  }, [dispatch]);

  useEffect(() => {
    if (examId) {
      dispatch(fetchResults({ exam_id: examId, per_page: 200 }));
    }
  }, [dispatch, examId]);

  const metrics = useMemo(() => {
    const total = results.length;
    const pass = results.filter((item) => item.result_status === 'pass').length;
    const fail = results.filter((item) => item.result_status === 'fail').length;
    const average = total ? results.reduce((sum, item) => sum + Number(item.percentage || 0), 0) / total : 0;
    return { total, pass, fail, average };
  }, [results]);

  return (
    <ExaminationPageShell
      title="Result Processing"
      description="Compute results against the grading system, review the outcome mix, then publish only when you’re confident the exam is ready."
      actions={canManage ? (
        <Stack direction={{ xs: 'column', sm: 'row' }} spacing={1.5}>
          <Button
            variant="contained"
            startIcon={<AutoFixHighOutlinedIcon />}
            disabled={!examId || saving}
            onClick={() => dispatch(computeResults({ examId: Number(examId), payload: { grading_system_id: gradingSystemId ? Number(gradingSystemId) : null } }))}
          >
            {saving ? 'Computing...' : 'Compute Results'}
          </Button>
          <Button
            variant="outlined"
            startIcon={<PublishOutlinedIcon />}
            disabled={!examId || saving}
            onClick={() => dispatch(publishResults({ examId: Number(examId), payload: { is_public: true, notify_users: true } }))}
          >
            Publish Results
          </Button>
        </Stack>
      ) : null}
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth select label="Exam" value={examId} onChange={(event) => setExamId(event.target.value)}>
              <MenuItem value="">Select</MenuItem>
              {exams.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth select label="Grading System" value={gradingSystemId} onChange={(event) => setGradingSystemId(event.target.value)}>
              <MenuItem value="">Use default active system</MenuItem>
              {gradingSystems.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
            </TextField>
          </Grid>
        </Grid>
        {error ? <Alert severity="error" sx={{ mt: 2 }}>{error}</Alert> : null}
      </Paper>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 3 }}>
          <PerformanceMetricCard label="Students Processed" value={metrics.total} helper="Total student results ready for review" progress={metrics.total ? 100 : 0} />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <PerformanceMetricCard label="Pass Count" value={metrics.pass} helper="Students currently cleared" progress={metrics.total ? (metrics.pass / metrics.total) * 100 : 0} color="success" />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <PerformanceMetricCard label="Fail Count" value={metrics.fail} helper="Students needing follow-up" progress={metrics.total ? (metrics.fail / metrics.total) * 100 : 0} color="error" />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <PerformanceMetricCard label="Average %" value={`${metrics.average.toFixed(2)}%`} helper="Class average across computed results" progress={metrics.average} />
        </Grid>
      </Grid>

      <AppDataTable
        title="Computed Results"
        columns={[
          { key: 'student', header: 'Student', render: (row) => row.student?.full_name || row.student?.name || 'N/A' },
          { key: 'obtained_marks', header: 'Obtained' },
          { key: 'total_marks', header: 'Total' },
          { key: 'percentage', header: 'Percentage', render: (row) => `${Number(row.percentage || 0).toFixed(2)}%` },
          { key: 'grade', header: 'Grade', render: (row) => row.grade || 'N/A' },
          { key: 'gpa', header: 'GPA', render: (row) => row.gpa ?? 'N/A' },
          { key: 'result_status', header: 'Status', render: (row) => <ResultStatusChip status={row.result_status} /> },
        ]}
        rows={results}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="Compute an exam to populate student results here."
      />
    </ExaminationPageShell>
  );
}
