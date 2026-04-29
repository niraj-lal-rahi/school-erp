import { Alert, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { ResultStatusChip } from '../components/ResultStatusChip';
import { fetchClassResults, fetchExaminationMasterData, fetchStudentResult } from '../store/examinationSlice';

export function ResultViewPage() {
  const dispatch = useAppDispatch();
  const { exams, schoolClasses, sections, students, classResults, selectedStudentResult, loading, error } = useAppSelector((state) => state.examination);
  const [examId, setExamId] = useState('');
  const [classId, setClassId] = useState('');
  const [sectionId, setSectionId] = useState('');
  const [studentId, setStudentId] = useState('');

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
  }, [dispatch]);

  const filteredSections = useMemo(
    () => sections.filter((section) => !classId || String(section.school_class_id) === String(classId)),
    [sections, classId],
  );

  useEffect(() => {
    if (examId && classId) {
      dispatch(fetchClassResults({ examId: Number(examId), classId: Number(classId), params: { section_id: sectionId || undefined, per_page: 200 } }));
    }
  }, [dispatch, examId, classId, sectionId]);

  useEffect(() => {
    if (examId && studentId) {
      dispatch(fetchStudentResult({ examId: Number(examId), studentId: Number(studentId) }));
    }
  }, [dispatch, examId, studentId]);

  return (
    <ExaminationPageShell
      title="Result View"
      description="Switch between class-level outcome review and a student-specific result view without leaving the exam workflow."
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth select label="Exam" value={examId} onChange={(event) => setExamId(event.target.value)}>
              <MenuItem value="">Select</MenuItem>
              {exams.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth select label="Class" value={classId} onChange={(event) => { setClassId(event.target.value); setSectionId(''); }}>
              <MenuItem value="">Select</MenuItem>
              {schoolClasses.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth select label="Section" value={sectionId} onChange={(event) => setSectionId(event.target.value)}>
              <MenuItem value="">All Sections</MenuItem>
              {filteredSections.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth select label="Student" value={studentId} onChange={(event) => setStudentId(event.target.value)}>
              <MenuItem value="">Optional student detail</MenuItem>
              {students.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name || item.name}</MenuItem>)}
            </TextField>
          </Grid>
        </Grid>
        {error ? <Alert severity="error" sx={{ mt: 2 }}>{error}</Alert> : null}
      </Paper>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Class Results"
            columns={[
              { key: 'student', header: 'Student', render: (row) => row.student?.full_name || row.student?.name || 'N/A' },
              { key: 'obtained_marks', header: 'Obtained' },
              { key: 'total_marks', header: 'Total' },
              { key: 'percentage', header: 'Percentage', render: (row) => `${Number(row.percentage || 0).toFixed(2)}%` },
              { key: 'grade', header: 'Grade', render: (row) => row.grade || 'N/A' },
              { key: 'rank', header: 'Rank', render: (row) => row.rank || '—' },
              { key: 'result_status', header: 'Status', render: (row) => <ResultStatusChip status={row.result_status} /> },
            ]}
            rows={classResults}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="Pick an exam and class to load results."
          />
        </Grid>

        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">Student Result Snapshot</Typography>
              {selectedStudentResult ? (
                <>
                  <Typography variant="subtitle1">{selectedStudentResult.student?.full_name || selectedStudentResult.student?.name}</Typography>
                  <Typography variant="body2" color="text.secondary">
                    Total: {selectedStudentResult.obtained_marks} / {selectedStudentResult.total_marks}
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    Percentage: {Number(selectedStudentResult.percentage || 0).toFixed(2)}%
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    Grade: {selectedStudentResult.grade || 'N/A'} {selectedStudentResult.gpa ? `• GPA ${selectedStudentResult.gpa}` : ''}
                  </Typography>
                  <ResultStatusChip status={selectedStudentResult.result_status} />
                </>
              ) : (
                <Typography variant="body2" color="text.secondary">
                  Select an exam and student to preview the individual result.
                </Typography>
              )}
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </ExaminationPageShell>
  );
}
