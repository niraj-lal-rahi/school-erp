import EmojiEventsOutlinedIcon from '@mui/icons-material/EmojiEventsOutlined';
import { Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { fetchExaminationMasterData, fetchMeritList } from '../store/examinationSlice';

export function MeritListPage() {
  const dispatch = useAppDispatch();
  const { exams, sections, meritList, loading } = useAppSelector((state) => state.examination);
  const [examId, setExamId] = useState('');
  const [sectionId, setSectionId] = useState('');

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
  }, [dispatch]);

  useEffect(() => {
    if (examId) {
      dispatch(fetchMeritList({ examId: Number(examId), params: { section_id: sectionId || undefined } }));
    }
  }, [dispatch, examId, sectionId]);

  const podium = useMemo(() => meritList.slice(0, 3), [meritList]);

  return (
    <ExaminationPageShell
      title="Merit List"
      description="Review rank ordering, tie-aware results, and top performers by exam before publishing or sharing reports."
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
            <TextField fullWidth select label="Section" value={sectionId} onChange={(event) => setSectionId(event.target.value)}>
              <MenuItem value="">All Sections</MenuItem>
              {sections.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
            </TextField>
          </Grid>
        </Grid>
      </Paper>

      <Grid container spacing={3}>
        {podium.map((item) => (
          <Grid key={item.id} size={{ xs: 12, md: 4 }}>
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack spacing={1}>
                <Stack direction="row" spacing={1} alignItems="center">
                  <EmojiEventsOutlinedIcon color="warning" />
                  <Typography variant="h6">Rank {item.rank}</Typography>
                </Stack>
                <Typography variant="subtitle1">{item.student?.full_name || item.student?.name || 'N/A'}</Typography>
                <Typography variant="body2" color="text.secondary">
                  {Number(item.percentage || 0).toFixed(2)}% • Grade {item.grade || 'N/A'}
                </Typography>
              </Stack>
            </Paper>
          </Grid>
        ))}
      </Grid>

      <AppDataTable
        title="Full Merit List"
        columns={[
          { key: 'rank', header: 'Rank' },
          { key: 'student', header: 'Student', render: (row) => row.student?.full_name || row.student?.name || 'N/A' },
          { key: 'obtained_marks', header: 'Obtained' },
          { key: 'total_marks', header: 'Total' },
          { key: 'percentage', header: 'Percentage', render: (row) => `${Number(row.percentage || 0).toFixed(2)}%` },
          { key: 'grade', header: 'Grade', render: (row) => row.grade || 'N/A' },
        ]}
        rows={meritList}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="Select an exam to compute and review the merit list."
      />
    </ExaminationPageShell>
  );
}
