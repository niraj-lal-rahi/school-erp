import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import RateReviewOutlinedIcon from '@mui/icons-material/RateReviewOutlined';
import { Chip, IconButton, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchMasterData } from '../../masterData/store/masterDataSlice';
import { AdmissionForm } from '../components/AdmissionForm';
import { createAdmission, fetchAdmissions, setAdmissionFilters, setAdmissionPage, updateAdmission } from '../store/admissionSlice';

export function AdmissionsPage() {
  const dispatch = useAppDispatch();
  const { items, filters, pagination, loading, saving, error } = useAppSelector((state) => state.admissions);
  const [editing, setEditing] = useState(null);

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchAdmissions({
      search: filters.search || undefined,
      application_status: filters.application_status || undefined,
      page: pagination.page,
    }));
  }, [dispatch, filters.search, filters.application_status, pagination.page]);

  async function handleSubmit(values) {
    const action = editing
      ? updateAdmission({ admissionId: editing.id, payload: values })
      : createAdmission(values);

    const result = await dispatch(action);
    if (!result.error) {
      setEditing(null);
      dispatch(fetchAdmissions({
        search: filters.search || undefined,
        application_status: filters.application_status || undefined,
        page: pagination.page,
      }));
    }
  }

  return (
    <Stack spacing={3}>
      <AdmissionForm
        title={editing ? 'Edit Admission Application' : 'New Admission Application'}
        initialValues={editing}
        onSubmit={handleSubmit}
        saving={saving}
        error={error}
      />

      <AppDataTable
        title="Admission Applications"
        columns={[
          {
            key: 'applicant',
            header: 'Applicant',
            render: (row) => (
              <Stack spacing={0.5}>
                <strong>{row.full_name}</strong>
                <span>{row.application_no}</span>
              </Stack>
            ),
          },
          { key: 'guardian_name', header: 'Guardian' },
          { key: 'guardian_phone', header: 'Phone' },
          { key: 'class', header: 'Applied Class', render: (row) => row.class?.name || 'Not selected' },
          { key: 'application_status', header: 'Status', render: (row) => <Chip size="small" label={row.application_status} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <IconButton color="secondary" onClick={() => setEditing(row)}>
                  <EditOutlinedIcon />
                </IconButton>
                <IconButton component={Link} to={`/student-admissions/${row.id}/review`} color="primary">
                  <RateReviewOutlinedIcon />
                </IconButton>
              </Stack>
            ),
          },
        ]}
        rows={items}
        loading={loading}
        searchValue={filters.search}
        onSearchChange={(search) => dispatch(setAdmissionFilters({ search }))}
        filters={[
          {
            key: 'application_status',
            label: 'Status',
            value: filters.application_status,
            onChange: (value) => dispatch(setAdmissionFilters({ application_status: value })),
            options: [
              { value: '', label: 'All statuses' },
              { value: 'draft', label: 'Draft' },
              { value: 'submitted', label: 'Submitted' },
              { value: 'under_review', label: 'Under Review' },
              { value: 'approved', label: 'Approved' },
              { value: 'rejected', label: 'Rejected' },
              { value: 'waitlisted', label: 'Waitlisted' },
              { value: 'converted', label: 'Converted' },
            ],
          },
        ]}
        pagination={{
          page: pagination.page,
          totalPages: pagination.totalPages,
          onPageChange: (page) => dispatch(setAdmissionPage(page)),
        }}
      />
    </Stack>
  );
}
