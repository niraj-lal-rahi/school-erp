import { Chip, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchMasterData } from '../../masterData/store/masterDataSlice';
import { fetchStudents } from '../../students/store/studentSlice';
import { createEnrollment, fetchEnrollments, setEnrollmentFilters, setEnrollmentPage, updateEnrollment } from '../store/enrollmentSlice';

function blankForm() {
  return {
    student_id: '',
    academic_year_id: '',
    school_class_id: '',
    section_id: '',
    roll_number: '',
    enrollment_date: '',
    joined_on: '',
    status: 'enrolled',
    is_current: true,
    remarks: '',
  };
}

export function EnrollmentsPage() {
  const dispatch = useAppDispatch();
  const { items, filters, pagination, loading, saving, error } = useAppSelector((state) => state.enrollments);
  const { items: students } = useAppSelector((state) => state.students);
  const { academicYears, classes } = useAppSelector((state) => state.masterData);
  const [form, setForm] = useState(blankForm());

  useEffect(() => {
    dispatch(fetchMasterData());
    dispatch(fetchStudents({ per_page: 100 }));
    dispatch(fetchEnrollments());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchEnrollments({
      search: filters.search || undefined,
      status: filters.status || undefined,
      page: pagination.page,
    }));
  }, [dispatch, filters.search, filters.status, pagination.page]);

  async function handleSubmit(event) {
    event.preventDefault();
    const action = form.id
      ? updateEnrollment({ enrollmentId: form.id, payload: form })
      : createEnrollment(form);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(blankForm());
      dispatch(fetchEnrollments({
        search: filters.search || undefined,
        status: filters.status || undefined,
        page: pagination.page,
      }));
    }
  }

  return (
    <Stack spacing={3}>
      <form onSubmit={handleSubmit}>
        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
          <select value={form.student_id} onChange={(e) => setForm((current) => ({ ...current, student_id: e.target.value }))}>
            <option value="">Student</option>
            {students.map((student) => <option key={student.id} value={student.id}>{student.full_name}</option>)}
          </select>
          <select value={form.academic_year_id} onChange={(e) => setForm((current) => ({ ...current, academic_year_id: e.target.value }))}>
            <option value="">Academic Year</option>
            {academicYears.map((year) => <option key={year.id} value={year.id}>{year.name}</option>)}
          </select>
          <select value={form.school_class_id} onChange={(e) => setForm((current) => ({ ...current, school_class_id: e.target.value }))}>
            <option value="">Class</option>
            {classes.map((schoolClass) => <option key={schoolClass.id} value={schoolClass.id}>{schoolClass.name}</option>)}
          </select>
          <input placeholder="Roll No" value={form.roll_number} onChange={(e) => setForm((current) => ({ ...current, roll_number: e.target.value }))} />
          <button type="submit" disabled={saving}>{saving ? 'Saving...' : 'Save Enrollment'}</button>
        </Stack>
        {error ? <div>{error}</div> : null}
      </form>

      <AppDataTable
        title="Enrollment History"
        columns={[
          { key: 'student', header: 'Student', render: (row) => row.student?.full_name || row.student_id },
          { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || row.academic_year_id },
          { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || row.school_class_id },
          { key: 'section', header: 'Section', render: (row) => row.section?.name || 'None' },
          { key: 'roll_number', header: 'Roll No' },
          { key: 'status', header: 'Status', render: (row) => <Chip size="small" label={row.status} color={row.is_current ? 'success' : 'default'} /> },
        ]}
        rows={items}
        loading={loading}
        searchValue={filters.search}
        onSearchChange={(search) => dispatch(setEnrollmentFilters({ search }))}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: filters.status,
            onChange: (status) => dispatch(setEnrollmentFilters({ status })),
            options: [
              { value: '', label: 'All statuses' },
              { value: 'pending', label: 'Pending' },
              { value: 'enrolled', label: 'Enrolled' },
              { value: 'promoted', label: 'Promoted' },
              { value: 'transferred', label: 'Transferred' },
              { value: 'withdrawn', label: 'Withdrawn' },
              { value: 'completed', label: 'Completed' },
            ],
          },
        ]}
        pagination={{
          page: pagination.page,
          totalPages: pagination.totalPages,
          onPageChange: (page) => dispatch(setEnrollmentPage(page)),
        }}
      />
    </Stack>
  );
}
