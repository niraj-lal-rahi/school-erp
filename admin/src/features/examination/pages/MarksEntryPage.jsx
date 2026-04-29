import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, Checkbox, Grid, MenuItem, Paper, Stack, Table, TableBody, TableCell, TableHead, TableRow, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { bulkStoreExamMarks, fetchExamMarks, fetchExamSubjects, fetchExaminationMasterData } from '../store/examinationSlice';

export function MarksEntryPage() {
  const dispatch = useAppDispatch();
  const { exams, students, examSubjects, examMarks, loading, saving, error } = useAppSelector((state) => state.examination);
  const canManage = useAppSelector((state) => state.auth.user?.permissions?.includes('exams.manage'));
  const [examId, setExamId] = useState('');
  const [subjectId, setSubjectId] = useState('');
  const [draftRows, setDraftRows] = useState([]);

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
  }, [dispatch]);

  useEffect(() => {
    if (examId) {
      dispatch(fetchExamSubjects(examId));
      dispatch(fetchExamMarks({ exam_id: examId, per_page: 200 }));
    }
  }, [dispatch, examId]);

  const selectedExam = useMemo(
    () => exams.find((item) => String(item.id) === String(examId)),
    [exams, examId],
  );

  const eligibleStudents = useMemo(() => students.filter((student) => {
    if (!selectedExam) return false;
    const classId = student.current_enrollment?.school_class_id || student.school_class_id;
    const sectionId = student.current_enrollment?.section_id || student.section_id;
    const classMatches = !selectedExam.class_id || String(classId || '') === String(selectedExam.class_id);
    const sectionMatches = !selectedExam.section_id || String(sectionId || '') === String(selectedExam.section_id);
    return classMatches && sectionMatches;
  }), [students, selectedExam]);

  useEffect(() => {
    if (!subjectId) {
      setDraftRows([]);
      return;
    }

    const nextRows = eligibleStudents.map((student) => {
      const existing = examMarks.find((mark) =>
        String(mark.student_id) === String(student.id) && String(mark.subject_id) === String(subjectId),
      );

      return {
        student_id: student.id,
        student_name: student.full_name || `${student.first_name || ''} ${student.last_name || ''}`.trim(),
        marks_obtained: existing?.marks_obtained ?? '',
        is_absent: Boolean(existing?.is_absent),
        remarks: existing?.remarks || '',
      };
    });

    setDraftRows(nextRows);
  }, [eligibleStudents, examMarks, subjectId]);

  function updateRow(studentId, key, value) {
    setDraftRows((current) => current.map((row) => (
      row.student_id === studentId ? { ...row, [key]: value } : row
    )));
  }

  async function handleBulkSave() {
    if (!canManage || !examId || !subjectId) return;

    const payload = {
      exam_id: Number(examId),
      records: draftRows.map((row) => ({
        student_id: row.student_id,
        subject_id: Number(subjectId),
        marks_obtained: row.is_absent || row.marks_obtained === '' ? null : Number(row.marks_obtained),
        is_absent: row.is_absent,
        remarks: row.remarks || null,
      })),
    };

    const result = await dispatch(bulkStoreExamMarks(payload));
    if (!result.error) {
      dispatch(fetchExamMarks({ exam_id: examId, per_page: 200 }));
    }
  }

  return (
    <ExaminationPageShell
      title="Marks Entry"
      description="Pick an exam and subject, then enter marks in a fast grid with absent handling for the whole class section in one pass."
      actions={canManage ? (
        <Button variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving || !subjectId} onClick={handleBulkSave}>
          {saving ? 'Saving...' : 'Save Bulk Marks'}
        </Button>
      ) : null}
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth select label="Exam" value={examId} onChange={(event) => { setExamId(event.target.value); setSubjectId(''); }}>
              <MenuItem value="">Select</MenuItem>
              {exams.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth select label="Subject" value={subjectId} onChange={(event) => setSubjectId(event.target.value)} disabled={!examId}>
              <MenuItem value="">Select</MenuItem>
              {examSubjects.map((item) => <MenuItem key={item.id} value={item.subject_id}>{item.subject?.name || `Subject #${item.subject_id}`}</MenuItem>)}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Stack spacing={0.5} justifyContent="center" sx={{ height: '100%' }}>
              <Typography variant="subtitle2">Students in grid</Typography>
              <Typography variant="body2" color="text.secondary">
                {draftRows.length} rows ready for entry
              </Typography>
            </Stack>
          </Grid>
        </Grid>

        {error ? <Alert severity="error" sx={{ mt: 2 }}>{error}</Alert> : null}
      </Paper>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Typography variant="h6" mb={2}>Bulk Marks Grid</Typography>
        <Table size="small">
          <TableHead>
            <TableRow>
              <TableCell>Student</TableCell>
              <TableCell>Marks</TableCell>
              <TableCell>Absent</TableCell>
              <TableCell>Remarks</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {draftRows.length ? draftRows.map((row) => (
              <TableRow key={row.student_id}>
                <TableCell>{row.student_name}</TableCell>
                <TableCell>
                  <TextField
                    size="small"
                    type="number"
                    value={row.marks_obtained}
                    disabled={row.is_absent}
                    onChange={(event) => updateRow(row.student_id, 'marks_obtained', event.target.value)}
                    sx={{ maxWidth: 120 }}
                  />
                </TableCell>
                <TableCell>
                  <Checkbox checked={row.is_absent} onChange={(event) => updateRow(row.student_id, 'is_absent', event.target.checked)} />
                </TableCell>
                <TableCell>
                  <TextField
                    size="small"
                    value={row.remarks}
                    onChange={(event) => updateRow(row.student_id, 'remarks', event.target.value)}
                    sx={{ minWidth: 220 }}
                  />
                </TableCell>
              </TableRow>
            )) : (
              <TableRow>
                <TableCell colSpan={4}>
                  <Typography py={3} textAlign="center" color="text.secondary">
                    {loading ? 'Loading marks grid...' : 'Select an exam and subject to start entering marks.'}
                  </Typography>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </Paper>
    </ExaminationPageShell>
  );
}
