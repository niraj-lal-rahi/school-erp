import PersonAddAltOutlinedIcon from '@mui/icons-material/PersonAddAltOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { enrollStudentsForExam, fetchExaminationMasterData } from '../store/examinationSlice';

export function StudentEnrollmentPage() {
  const dispatch = useAppDispatch();
  const { exams, students, loading, saving, error } = useAppSelector((state) => state.examination);
  const canManage = useAppSelector((state) => state.auth.user?.permissions?.includes('exams.manage'));
  const [examId, setExamId] = useState('');

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
  }, [dispatch]);

  const selectedExam = useMemo(
    () => exams.find((item) => String(item.id) === String(examId)),
    [exams, examId],
  );

  const enrolledRows = useMemo(() => {
    const selectedStudents = students.filter((student) => {
      const classMatches = !selectedExam?.class_id || String(student.current_enrollment?.school_class_id || student.school_class_id || '') === String(selectedExam.class_id);
      const sectionMatches = !selectedExam?.section_id || String(student.current_enrollment?.section_id || student.section_id || '') === String(selectedExam.section_id);
      return classMatches && sectionMatches;
    });

    return selectedStudents.map((student) => ({
      id: student.id,
      full_name: student.full_name || `${student.first_name || ''} ${student.last_name || ''}`.trim(),
      admission_no: student.admission_no || 'N/A',
      class_name: student.current_enrollment?.school_class?.name || student.school_class?.name || 'N/A',
      section_name: student.current_enrollment?.section?.name || student.section?.name || 'N/A',
    }));
  }, [students, selectedExam]);

  return (
    <ExaminationPageShell
      title="Student Enrollment"
      description="Prepare exam participation lists from the current SIS enrollment and quickly enroll an entire class or section into the selected exam."
      actions={canManage ? (
        <Button
          variant="contained"
          startIcon={<PersonAddAltOutlinedIcon />}
          disabled={!examId || saving}
          onClick={() => dispatch(enrollStudentsForExam(Number(examId)))}
        >
          {saving ? 'Enrolling...' : 'Enroll Students'}
        </Button>
      ) : null}
    >
      <Grid container spacing={3}>
        <Grid size={{ xs: 12 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ xs: 'stretch', md: 'center' }}>
              <TextField
                select
                label="Select Exam"
                value={examId}
                onChange={(event) => setExamId(event.target.value)}
                sx={{ minWidth: 320 }}
              >
                <MenuItem value="">Select</MenuItem>
                {exams.map((item) => (
                  <MenuItem key={item.id} value={item.id}>
                    {item.name}
                  </MenuItem>
                ))}
              </TextField>

              {selectedExam ? (
                <Stack spacing={0.5}>
                  <Typography variant="subtitle1">{selectedExam.name}</Typography>
                  <Typography variant="body2" color="text.secondary">
                    {selectedExam.school_class?.name || 'All Classes'} {selectedExam.section?.name ? `• ${selectedExam.section.name}` : ''}
                  </Typography>
                </Stack>
              ) : null}
            </Stack>
            {error ? <Alert severity="error" sx={{ mt: 2 }}>{error}</Alert> : null}
          </Paper>
        </Grid>

        <Grid size={{ xs: 12 }}>
          <AppDataTable
            title="Eligible Students"
            columns={[
              { key: 'full_name', header: 'Student' },
              { key: 'admission_no', header: 'Admission No' },
              { key: 'class_name', header: 'Class' },
              { key: 'section_name', header: 'Section' },
            ]}
            rows={examId ? enrolledRows : []}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState={examId ? 'No eligible students found for this exam setup.' : 'Select an exam to preview the target student set.'}
          />
        </Grid>
      </Grid>
    </ExaminationPageShell>
  );
}
