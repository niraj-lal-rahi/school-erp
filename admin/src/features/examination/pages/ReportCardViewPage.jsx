import PrintOutlinedIcon from '@mui/icons-material/PrintOutlined';
import { Button, Grid, MenuItem, Paper, Stack, Table, TableBody, TableCell, TableHead, TableRow, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { ResultStatusChip } from '../components/ResultStatusChip';
import { fetchExaminationMasterData, fetchStudentResult } from '../store/examinationSlice';

export function ReportCardViewPage() {
  const dispatch = useAppDispatch();
  const { exams, students, selectedStudentResult, loading } = useAppSelector((state) => state.examination);
  const [examId, setExamId] = useState('');
  const [studentId, setStudentId] = useState('');

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
  }, [dispatch]);

  useEffect(() => {
    if (examId && studentId) {
      dispatch(fetchStudentResult({ examId: Number(examId), studentId: Number(studentId) }));
    }
  }, [dispatch, examId, studentId]);

  return (
    <ExaminationPageShell
      title="Report Card View"
      description="Preview a printable report card layout for a student result, ready for PDF generation once the backend export flow is expanded."
      actions={selectedStudentResult ? (
        <Button variant="contained" startIcon={<PrintOutlinedIcon />} onClick={() => window.print()}>
          Print Report Card
        </Button>
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
            <TextField fullWidth select label="Student" value={studentId} onChange={(event) => setStudentId(event.target.value)}>
              <MenuItem value="">Select</MenuItem>
              {students.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name || item.name}</MenuItem>)}
            </TextField>
          </Grid>
        </Grid>
      </Paper>

      <Paper elevation={0} sx={{ p: 4, border: '1px solid rgba(20,33,61,0.08)' }}>
        {loading && !selectedStudentResult ? (
          <Typography color="text.secondary">Loading report card...</Typography>
        ) : selectedStudentResult ? (
          <Stack spacing={3}>
            <Stack spacing={0.5}>
              <Typography variant="h4">Report Card</Typography>
              <Typography variant="subtitle1">{selectedStudentResult.student?.full_name || selectedStudentResult.student?.name}</Typography>
              <Typography variant="body2" color="text.secondary">
                Exam: {selectedStudentResult.exam?.name || 'N/A'}
              </Typography>
            </Stack>

            <Grid container spacing={2}>
              <Grid size={{ xs: 12, md: 3 }}>
                <Typography variant="body2" color="text.secondary">Total Marks</Typography>
                <Typography variant="h6">{selectedStudentResult.total_marks}</Typography>
              </Grid>
              <Grid size={{ xs: 12, md: 3 }}>
                <Typography variant="body2" color="text.secondary">Obtained</Typography>
                <Typography variant="h6">{selectedStudentResult.obtained_marks}</Typography>
              </Grid>
              <Grid size={{ xs: 12, md: 3 }}>
                <Typography variant="body2" color="text.secondary">Percentage</Typography>
                <Typography variant="h6">{Number(selectedStudentResult.percentage || 0).toFixed(2)}%</Typography>
              </Grid>
              <Grid size={{ xs: 12, md: 3 }}>
                <Typography variant="body2" color="text.secondary">Status</Typography>
                <ResultStatusChip status={selectedStudentResult.result_status} />
              </Grid>
            </Grid>

            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Subject</TableCell>
                  <TableCell>Max Marks</TableCell>
                  <TableCell>Obtained</TableCell>
                  <TableCell>Grade</TableCell>
                  <TableCell>Pass</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {(selectedStudentResult.subject_details || selectedStudentResult.result_subject_details || []).map((detail) => (
                  <TableRow key={detail.id}>
                    <TableCell>{detail.subject?.name || `Subject #${detail.subject_id}`}</TableCell>
                    <TableCell>{detail.max_marks}</TableCell>
                    <TableCell>{detail.obtained_marks}</TableCell>
                    <TableCell>{detail.grade || 'N/A'}</TableCell>
                    <TableCell>{detail.is_pass ? 'Yes' : 'No'}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </Stack>
        ) : (
          <Typography color="text.secondary">
            Select an exam and student to render the report card preview.
          </Typography>
        )}
      </Paper>
    </ExaminationPageShell>
  );
}
