import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import {
  Button,
  Chip,
  IconButton,
  Paper,
  Stack,
} from '@mui/material';
import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchMasterData } from '../../masterData/store/masterDataSlice';
import { StudentStatusChip } from '../components/StudentStatusChip';
import { fetchStudents, setStudentFilters, setStudentPage } from '../store/studentSlice';

export function StudentListPage() {
  const dispatch = useAppDispatch();
  const { items, loading, filters, pagination } = useAppSelector((state) => state.students);
  const { academicYears, classes, sections, studentCategories, studentHouses } = useAppSelector((state) => state.masterData);

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(
      fetchStudents({
        search: filters.search || undefined,
        status: filters.status || undefined,
        academic_year_id: filters.academic_year_id || undefined,
        school_class_id: filters.school_class_id || undefined,
        section_id: filters.section_id || undefined,
        category_id: filters.category_id || undefined,
        house_id: filters.house_id || undefined,
        page: pagination.page,
      })
    );
  }, [
    dispatch,
    filters.search,
    filters.status,
    filters.academic_year_id,
    filters.school_class_id,
    filters.section_id,
    filters.category_id,
    filters.house_id,
    pagination.page,
  ]);

  const columns = [
    {
      key: 'student',
      header: 'Student',
      render: (row) => (
        <Stack spacing={0.5}>
          <strong>{row.full_name || `${row.first_name} ${row.last_name}`}</strong>
          <span>{row.admission_no}{row.roll_no ? ` • Roll ${row.roll_no}` : ''}</span>
        </Stack>
      ),
    },
    {
      key: 'status',
      header: 'Lifecycle',
      render: (row) => <StudentStatusChip status={row.current_status || row.status} />,
    },
    {
      key: 'enrollment',
      header: 'Current Placement',
      render: (row) => {
        const currentEnrollment = (row.enrollments || []).find((item) => item.is_current) || row.enrollments?.[0];
        if (!currentEnrollment) {
          return 'Not assigned';
        }

        const sectionLabel = currentEnrollment.section?.name ? ` • ${currentEnrollment.section.name}` : '';
        return `${currentEnrollment.school_class?.name || 'Class'}${sectionLabel}`;
      },
    },
    {
      key: 'category',
      header: 'Category',
      render: (row) => row.category?.name || 'n/a',
    },
    {
      key: 'house',
      header: 'House',
      render: (row) => row.house?.name || 'n/a',
    },
    {
      key: 'guardianCount',
      header: 'Guardians',
      render: (row) => <Chip label={`${row.guardians?.length || 0} mapped`} variant="outlined" />,
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (row) => (
        <Stack direction="row" spacing={1}>
          <IconButton component={Link} to={`/students/${row.id}`} color="primary">
            <VisibilityOutlinedIcon />
          </IconButton>
          <PermissionGate permission="students.update" fallback={null}>
            <IconButton component={Link} to={`/students/${row.id}/edit`} color="secondary">
              <EditOutlinedIcon />
            </IconButton>
          </PermissionGate>
        </Stack>
      ),
    },
  ];

  const sectionOptions = sections
    .filter((section) => !filters.school_class_id || Number(section.school_class_id) === Number(filters.school_class_id))
    .map((section) => ({ value: section.id, label: section.name }));

  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2}>
          <div>
            <strong>छात्र सूची</strong>
            <div>Search the student directory by lifecycle, academic placement, category, house, and core identity fields.</div>
          </div>
          <PermissionGate permission="students.create" fallback={null}>
            <Button component={Link} to="/students/new" variant="contained" startIcon={<AddOutlinedIcon />}>
              Add Student
            </Button>
          </PermissionGate>
        </Stack>
      </Paper>

      <AppDataTable
        title="Student Directory"
        columns={columns}
        rows={items}
        loading={loading}
        searchValue={filters.search}
        onSearchChange={(search) => dispatch(setStudentFilters({ search }))}
        filters={[
          {
            key: 'status',
            label: 'Lifecycle',
            value: filters.status,
            onChange: (status) => dispatch(setStudentFilters({ status })),
            options: [
              { value: '', label: 'All statuses' },
              { value: 'applicant', label: 'Applicant' },
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
              { value: 'withdrawn', label: 'Withdrawn' },
              { value: 'suspended', label: 'Suspended' },
              { value: 'alumni', label: 'Alumni' },
            ],
          },
          {
            key: 'academic_year_id',
            label: 'Academic Year',
            value: filters.academic_year_id,
            onChange: (academic_year_id) => dispatch(setStudentFilters({ academic_year_id })),
            options: [
              { value: '', label: 'All years' },
              ...academicYears.map((year) => ({ value: year.id, label: year.name })),
            ],
          },
          {
            key: 'school_class_id',
            label: 'Class',
            value: filters.school_class_id,
            onChange: (school_class_id) => dispatch(setStudentFilters({ school_class_id, section_id: '' })),
            options: [
              { value: '', label: 'All classes' },
              ...classes.map((schoolClass) => ({ value: schoolClass.id, label: `${schoolClass.name} (${schoolClass.code})` })),
            ],
          },
          {
            key: 'section_id',
            label: 'Section',
            value: filters.section_id,
            onChange: (section_id) => dispatch(setStudentFilters({ section_id })),
            options: [
              { value: '', label: 'All sections' },
              ...sectionOptions,
            ],
          },
          {
            key: 'category_id',
            label: 'Category',
            value: filters.category_id,
            onChange: (category_id) => dispatch(setStudentFilters({ category_id })),
            options: [
              { value: '', label: 'All categories' },
              ...studentCategories.map((category) => ({ value: category.id, label: category.name })),
            ],
          },
          {
            key: 'house_id',
            label: 'House',
            value: filters.house_id,
            onChange: (house_id) => dispatch(setStudentFilters({ house_id })),
            options: [
              { value: '', label: 'All houses' },
              ...studentHouses.map((house) => ({ value: house.id, label: house.name })),
            ],
          },
        ]}
        pagination={{
          page: pagination.page,
          totalPages: pagination.totalPages,
          onPageChange: (page) => dispatch(setStudentPage(page)),
        }}
      />
    </Stack>
  );
}
