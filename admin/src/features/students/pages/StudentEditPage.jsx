import { CircularProgress, Stack } from '@mui/material';
import { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchMasterData } from '../../masterData/store/masterDataSlice';
import { StudentForm } from '../components/StudentForm';
import { fetchStudentById, updateStudent } from '../store/studentSlice';

export function StudentEditPage() {
  const { studentId } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { currentStudent, loading, saving, error } = useAppSelector((state) => state.students);

  useEffect(() => {
    dispatch(fetchMasterData());
    dispatch(fetchStudentById(studentId));
  }, [dispatch, studentId]);

  const initialValues = currentStudent
    ? {
        ...currentStudent,
        guardians: (currentStudent.guardians || []).map((guardian) => ({
          id: guardian.id,
          relationship: guardian.pivot?.relationship,
          relationship_label: guardian.pivot?.relationship_label,
          is_primary: guardian.pivot?.is_primary,
          is_emergency_contact: guardian.pivot?.is_emergency_contact,
          pickup_authorized: guardian.pivot?.pickup_authorized,
        })),
        enrollment: currentStudent.enrollments?.[0]
          ? {
              academic_year_id: currentStudent.enrollments[0].academic_year_id || '',
              school_class_id: currentStudent.enrollments[0].school_class_id || '',
              section_id: currentStudent.enrollments[0].section_id || '',
              roll_number: currentStudent.enrollments[0].roll_number || '',
              status: currentStudent.enrollments[0].status || 'active',
              joined_on: currentStudent.enrollments[0].joined_on || '',
            }
          : undefined,
        admission: currentStudent.admissions?.[0]
          ? {
              academic_year_id: currentStudent.admissions[0].academic_year_id || '',
              applied_class_id: currentStudent.admissions[0].applied_class_id || '',
              status: currentStudent.admissions[0].status || 'applied',
              applied_on: currentStudent.admissions[0].applied_on || '',
              admitted_on: currentStudent.admissions[0].admitted_on || '',
              remarks: currentStudent.admissions[0].remarks || '',
            }
          : undefined,
      }
    : null;

  async function handleSubmit(values) {
    const result = await dispatch(updateStudent({ studentId, payload: values }));

    if (!result.error) {
      navigate(`/students/${studentId}`);
    }
  }

  if (loading || !currentStudent) {
    return (
      <Stack alignItems="center" py={8}>
        <CircularProgress />
      </Stack>
    );
  }

  return <StudentForm title="Edit Student" initialValues={initialValues} onSubmit={handleSubmit} saving={saving} error={error} />;
}
