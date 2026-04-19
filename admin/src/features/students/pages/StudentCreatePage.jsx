import { Alert } from '@mui/material';
import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchMasterData } from '../../masterData/store/masterDataSlice';
import { StudentForm } from '../components/StudentForm';
import { createStudent } from '../store/studentSlice';

export function StudentCreatePage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { saving, error } = useAppSelector((state) => state.students);

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  async function handleSubmit(values) {
    const result = await dispatch(createStudent(values));

    if (!result.error) {
      navigate(`/students/${result.payload.id}`);
    }
  }

  return (
    <>
      <Alert severity="info" sx={{ mb: 3 }}>
        This form creates the student, enrollment, admission record, and guardian mapping in one request.
      </Alert>
      <StudentForm title="Add Student" onSubmit={handleSubmit} saving={saving} error={error} />
    </>
  );
}
