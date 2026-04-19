import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import {
  Alert,
  Button,
  CircularProgress,
  Grid,
  Stack,
  Tab,
  Tabs,
} from '@mui/material';
import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentUploadPanel } from '../components/DocumentUploadPanel';
import { StudentDocumentsPanel } from '../components/StudentDocumentsPanel';
import { StudentEnrollmentPanel } from '../components/StudentEnrollmentPanel';
import { StudentGuardiansPanel } from '../components/StudentGuardiansPanel';
import { StudentLifecyclePanel } from '../components/StudentLifecyclePanel';
import { StudentMedicalPanel } from '../components/StudentMedicalPanel';
import { StudentNotesPanel } from '../components/StudentNotesPanel';
import { StudentProfileCard } from '../components/StudentProfileCard';
import { StudentStatusHistoryPanel } from '../components/StudentStatusHistoryPanel';
import {
  addStudentNote,
  deleteStudentDocument,
  deleteStudentNote,
  fetchStudentById,
  graduateStudent,
  promoteStudent,
  reactivateStudent,
  saveStudentMedical,
  suspendStudent,
  transferStudentSection,
  uploadStudentDocument,
  withdrawStudent,
} from '../store/studentSlice';

export function StudentProfilePage() {
  const { studentId } = useParams();
  const dispatch = useAppDispatch();
  const { currentStudent, loading, saving, error } = useAppSelector((state) => state.students);
  const [tab, setTab] = useState('overview');

  useEffect(() => {
    dispatch(fetchStudentById(studentId));
  }, [dispatch, studentId]);

  async function handleUpload(payload) {
    await dispatch(uploadStudentDocument({ studentId, payload }));
    dispatch(fetchStudentById(studentId));
  }

  async function handleDeleteDocument(documentId) {
    await dispatch(deleteStudentDocument(documentId));
    dispatch(fetchStudentById(studentId));
  }

  async function handleSaveMedical(payload) {
    await dispatch(saveStudentMedical({ studentId, payload }));
    dispatch(fetchStudentById(studentId));
  }

  async function handleCreateNote(payload) {
    await dispatch(addStudentNote({ studentId, payload }));
    dispatch(fetchStudentById(studentId));
  }

  async function handleDeleteNote(noteId) {
    await dispatch(deleteStudentNote(noteId));
    dispatch(fetchStudentById(studentId));
  }

  async function handleLifecycleAction(action, payload) {
    const actions = {
      promote: promoteStudent,
      transfer_section: transferStudentSection,
      withdraw: withdrawStudent,
      graduate: graduateStudent,
      suspend: suspendStudent,
      reactivate: reactivateStudent,
    };

    const thunk = actions[action];
    if (!thunk) {
      return;
    }

    await dispatch(thunk({ studentId, payload }));
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
        <Grid size={{ xs: 12 }}>
          <Tabs value={tab} onChange={(_, nextValue) => setTab(nextValue)} variant="scrollable" allowScrollButtonsMobile>
            <Tab value="overview" label="Overview" />
            <Tab value="guardians" label="Guardians" />
            <Tab value="enrollment" label="Enrollment" />
            <Tab value="documents" label="Documents" />
            <Tab value="medical" label="Medical" />
            <Tab value="notes" label="Notes" />
            <Tab value="status-history" label="Status History" />
            <Tab value="lifecycle" label="Lifecycle" />
          </Tabs>
        </Grid>

        {tab === 'overview' ? (
          <Grid size={{ xs: 12 }}>
            <StudentProfileCard student={currentStudent} />
          </Grid>
        ) : null}

        {tab === 'guardians' ? (
          <Grid size={{ xs: 12 }}>
            <StudentGuardiansPanel guardians={currentStudent.guardians || []} />
          </Grid>
        ) : null}

        {tab === 'enrollment' ? (
          <Grid size={{ xs: 12 }}>
            <StudentEnrollmentPanel enrollments={currentStudent.enrollments || []} />
          </Grid>
        ) : null}

        {tab === 'documents' ? (
          <>
            <Grid size={{ xs: 12, lg: 8 }}>
              <StudentDocumentsPanel
                documents={currentStudent.documents || []}
                onDelete={handleDeleteDocument}
                saving={saving}
                error={error}
              />
            </Grid>
            <Grid size={{ xs: 12, lg: 4 }}>
              <PermissionGate permission="students.documents.upload">
                <DocumentUploadPanel onUpload={handleUpload} saving={saving} error={error} disabled={saving} />
              </PermissionGate>
            </Grid>
          </>
        ) : null}

        {tab === 'medical' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate
              permission="students.medical.manage"
              fallback={<StudentMedicalPanel record={currentStudent.medical_record} onSave={() => {}} saving={false} error={error} readOnly />}
            >
              <StudentMedicalPanel
                record={currentStudent.medical_record}
                onSave={handleSaveMedical}
                saving={saving}
                error={error}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'lifecycle' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="students.update" fallback={null}>
              <StudentLifecyclePanel
                student={currentStudent}
                onSubmit={handleLifecycleAction}
                saving={saving}
                error={error}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'status-history' ? (
          <Grid size={{ xs: 12 }}>
            <StudentStatusHistoryPanel items={currentStudent.status_history || []} />
          </Grid>
        ) : null}

        {tab === 'notes' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="students.update" fallback={null}>
              <StudentNotesPanel
                notes={currentStudent.notes_entries || []}
                onCreate={handleCreateNote}
                onDelete={handleDeleteNote}
                saving={saving}
                error={error}
              />
            </PermissionGate>
          </Grid>
        ) : null}
      </Grid>
    </Stack>
  );
}
