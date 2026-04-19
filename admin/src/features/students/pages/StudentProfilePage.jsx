import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import {
  Alert,
  Button,
  CircularProgress,
  Grid,
  Stack,
} from '@mui/material';
import { useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentUploadPanel } from '../components/DocumentUploadPanel';
import { StudentProfileCard } from '../components/StudentProfileCard';
import { fetchStudentById, uploadStudentDocument } from '../store/studentSlice';

export function StudentProfilePage() {
  const { studentId } = useParams();
  const dispatch = useAppDispatch();
  const { currentStudent, loading, saving, error } = useAppSelector((state) => state.students);

  useEffect(() => {
    dispatch(fetchStudentById(studentId));
  }, [dispatch, studentId]);

  async function handleUpload(payload) {
    await dispatch(uploadStudentDocument({ studentId, payload }));
    dispatch(fetchStudentById(studentId));
  }

  if (loading || !currentStudent) {
    return (
      <Stack alignItems="center" py={8}>
        <CircularProgress />
      </Stack>
    );
  }

  return (
    <Stack spacing={3}>
      <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2}>
        <Alert severity="success">
          Student lifecycle is tracked from the same profile. Switch status between active, inactive, and alumni from the edit screen.
        </Alert>
        <PermissionGate permission="students.update" fallback={null}>
          <Button component={Link} to={`/students/${studentId}/edit`} variant="contained" startIcon={<EditOutlinedIcon />}>
            Edit Student
          </Button>
        </PermissionGate>
      </Stack>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 8 }}>
          <StudentProfileCard student={currentStudent} />
        </Grid>
        <Grid size={{ xs: 12, lg: 4 }}>
          <PermissionGate permission="students.documents.upload">
            <DocumentUploadPanel onUpload={handleUpload} saving={saving} error={error} disabled={saving} />
          </PermissionGate>
        </Grid>
      </Grid>
    </Stack>
  );
}
