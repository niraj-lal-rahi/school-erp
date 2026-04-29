import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { createExamSubject, deleteExamSubject, fetchExamSubjects, fetchExaminationMasterData } from '../store/examinationSlice';

const initialForm = {
  exam_id: '',
  subject_id: '',
  max_marks: '',
  passing_marks: '',
  weightage: '',
};

export function SubjectMappingPage() {
  const dispatch = useAppDispatch();
  const { exams, subjects, examSubjects, loading, saving, error } = useAppSelector((state) => state.examination);
  const canManage = useAppSelector((state) => state.auth.user?.permissions?.includes('exams.manage'));
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
  }, [dispatch]);

  useEffect(() => {
    if (form.exam_id) {
      dispatch(fetchExamSubjects(form.exam_id));
    }
  }, [dispatch, form.exam_id]);

  const selectedExam = useMemo(
    () => exams.find((item) => String(item.id) === String(form.exam_id)),
    [exams, form.exam_id],
  );

  const filteredSubjects = useMemo(() => {
    if (!selectedExam?.class_id) {
      return subjects;
    }

    return subjects.filter((subject) => {
      if (!subject.class_subjects?.length) return true;
      return subject.class_subjects.some((item) => String(item.school_class_id) === String(selectedExam.class_id));
    });
  }, [subjects, selectedExam]);

  async function handleSubmit(event) {
    event.preventDefault();
    if (!canManage) return;

    const result = await dispatch(createExamSubject({
      exam_id: Number(form.exam_id),
      subject_id: Number(form.subject_id),
      max_marks: Number(form.max_marks),
      passing_marks: form.passing_marks === '' ? null : Number(form.passing_marks),
      weightage: form.weightage === '' ? null : Number(form.weightage),
    }));

    if (!result.error) {
      setForm((current) => ({ ...initialForm, exam_id: current.exam_id }));
      dispatch(fetchExamSubjects(form.exam_id));
    }
  }

  return (
    <ExaminationPageShell
      title="Subject Mapping"
      description="Attach subjects to each exam with their max marks, pass marks, and optional weightage before marks entry begins."
    >
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleSubmit}>
              <Typography variant="h6">Map Exam Subject</Typography>
              {error ? <Alert severity="error">{error}</Alert> : null}
              <TextField select label="Exam" value={form.exam_id} onChange={(event) => setForm((current) => ({ ...current, exam_id: event.target.value, subject_id: '' }))}>
                <MenuItem value="">Select</MenuItem>
                {exams.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Subject" value={form.subject_id} onChange={(event) => setForm((current) => ({ ...current, subject_id: event.target.value }))} disabled={!form.exam_id}>
                <MenuItem value="">Select</MenuItem>
                {filteredSubjects.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField type="number" label="Max Marks" value={form.max_marks} onChange={(event) => setForm((current) => ({ ...current, max_marks: event.target.value }))} />
              <TextField type="number" label="Passing Marks" value={form.passing_marks} onChange={(event) => setForm((current) => ({ ...current, passing_marks: event.target.value }))} />
              <TextField type="number" label="Weightage" value={form.weightage} onChange={(event) => setForm((current) => ({ ...current, weightage: event.target.value }))} />
              <Button type="submit" variant="contained" disabled={saving || !canManage || !form.exam_id}>
                {saving ? 'Saving...' : 'Attach Subject'}
              </Button>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Mapped Subjects"
            columns={[
              { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || 'N/A' },
              { key: 'max_marks', header: 'Max Marks' },
              { key: 'passing_marks', header: 'Pass Marks' },
              { key: 'weightage', header: 'Weightage' },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => canManage ? (
                  <IconButton color="error" onClick={async () => {
                    await dispatch(deleteExamSubject({ id: row.id, examId: form.exam_id }));
                    dispatch(fetchExamSubjects(form.exam_id));
                  }}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                ) : 'View only',
              },
            ]}
            rows={examSubjects}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState={form.exam_id ? 'No subjects mapped yet.' : 'Select an exam to view its mapped subjects.'}
          />
        </Grid>
      </Grid>
    </ExaminationPageShell>
  );
}
