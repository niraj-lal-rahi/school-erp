import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { createRevaluation, deleteRevaluation, fetchExaminationMasterData, fetchRevaluations } from '../store/examinationSlice';

const initialForm = {
  exam_id: '',
  student_id: '',
  subject_id: '',
  reason: '',
};

export function RevaluationRequestsPage() {
  const dispatch = useAppDispatch();
  const { exams, students, subjects, revaluations, revaluationsPagination, loading, saving, error } = useAppSelector((state) => state.examination);
  const canManage = useAppSelector((state) => state.auth.user?.permissions?.includes('exams.manage'));
  const [form, setForm] = useState(initialForm);
  const [page, setPage] = useState(1);
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchRevaluations({ per_page: 15, page, status: statusFilter || undefined }));
  }, [dispatch, page, statusFilter]);

  const examSubjects = useMemo(() => {
    const selectedExam = exams.find((item) => String(item.id) === String(form.exam_id));
    return selectedExam?.exam_subjects || [];
  }, [exams, form.exam_id]);

  async function handleSubmit(event) {
    event.preventDefault();
    if (!canManage) return;

    const result = await dispatch(createRevaluation({
      exam_id: Number(form.exam_id),
      student_id: Number(form.student_id),
      subject_id: Number(form.subject_id),
      reason: form.reason,
    }));

    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchRevaluations({ per_page: 15, page: 1, status: statusFilter || undefined }));
      setPage(1);
    }
  }

  return (
    <ExaminationPageShell
      title="Revaluation Requests"
      description="Track student requests for answer-script review and keep the request queue visible alongside current exam context."
    >
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleSubmit}>
              <Typography variant="h6">New Revaluation Request</Typography>
              {error ? <Alert severity="error">{error}</Alert> : null}
              <TextField select label="Exam" value={form.exam_id} onChange={(event) => setForm((current) => ({ ...current, exam_id: event.target.value, subject_id: '' }))}>
                <MenuItem value="">Select</MenuItem>
                {exams.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Student" value={form.student_id} onChange={(event) => setForm((current) => ({ ...current, student_id: event.target.value }))}>
                <MenuItem value="">Select</MenuItem>
                {students.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name || item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Subject" value={form.subject_id} onChange={(event) => setForm((current) => ({ ...current, subject_id: event.target.value }))}>
                <MenuItem value="">Select</MenuItem>
                {(examSubjects.length ? examSubjects.map((item) => ({
                  id: item.subject_id,
                  name: item.subject?.name || `Subject #${item.subject_id}`,
                })) : subjects).map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
              <TextField label="Reason" multiline minRows={4} value={form.reason} onChange={(event) => setForm((current) => ({ ...current, reason: event.target.value }))} />
              <Button type="submit" variant="contained" disabled={saving || !canManage}>
                {saving ? 'Saving...' : 'Create Request'}
              </Button>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Revaluation Queue"
            columns={[
              { key: 'exam', header: 'Exam', render: (row) => row.exam?.name || 'N/A' },
              { key: 'student', header: 'Student', render: (row) => row.student?.full_name || row.student?.name || 'N/A' },
              { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || 'N/A' },
              { key: 'status', header: 'Status' },
              { key: 'requested_at', header: 'Requested At' },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => canManage ? (
                  <IconButton color="error" onClick={() => dispatch(deleteRevaluation(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                ) : 'View only',
              },
            ]}
            rows={revaluations}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            filters={[
              {
                key: 'status',
                label: 'Status',
                value: statusFilter,
                onChange: (value) => {
                  setStatusFilter(value);
                  setPage(1);
                },
                options: [
                  { value: '', label: 'All' },
                  { value: 'pending', label: 'Pending' },
                  { value: 'approved', label: 'Approved' },
                  { value: 'rejected', label: 'Rejected' },
                  { value: 'completed', label: 'Completed' },
                ],
              },
            ]}
            pagination={{
              page,
              totalPages: revaluationsPagination.totalPages,
              onPageChange: setPage,
            }}
            emptyState="No revaluation requests yet."
          />
        </Grid>
      </Grid>
    </ExaminationPageShell>
  );
}
