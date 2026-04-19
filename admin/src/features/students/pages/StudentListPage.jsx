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
import { StudentStatusChip } from '../components/StudentStatusChip';
import { fetchStudents, setStudentFilters, setStudentPage } from '../store/studentSlice';

export function StudentListPage() {
  const dispatch = useAppDispatch();
  const { items, loading, filters, pagination } = useAppSelector((state) => state.students);

  useEffect(() => {
    dispatch(
      fetchStudents({
        search: filters.search || undefined,
        status: filters.status || undefined,
        page: pagination.page,
      })
    );
  }, [dispatch, filters.search, filters.status, pagination.page]);

  const columns = [
    {
      key: 'student',
      header: 'Student',
      render: (row) => (
        <Stack spacing={0.5}>
          <strong>{row.full_name || `${row.first_name} ${row.last_name}`}</strong>
          <span>{row.admission_no}</span>
        </Stack>
      ),
    },
    {
      key: 'status',
      header: 'Lifecycle',
      render: (row) => <StudentStatusChip status={row.status} />,
    },
    {
      key: 'guardianCount',
      header: 'Guardians',
      render: (row) => <Chip label={`${row.guardians?.length || 0} mapped`} variant="outlined" />,
    },
    {
      key: 'admission',
      header: 'Admission',
      render: (row) => row.admissions?.[0]?.status || 'n/a',
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

  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2}>
          <div>
            <strong>छात्र सूची</strong>
            <div>Manage active students, inactive records, and alumni transitions from one place.</div>
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
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
              { value: 'alumni', label: 'Alumni' },
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
