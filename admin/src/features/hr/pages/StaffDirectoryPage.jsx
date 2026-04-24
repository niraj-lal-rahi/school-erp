import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import OpenInNewOutlinedIcon from '@mui/icons-material/OpenInNewOutlined';
import { Alert, Button, Chip, IconButton, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createHrResource,
  deleteHrResource,
  fetchHrOptions,
  fetchHrResource,
  setHrResourceFilters,
  setHrResourcePage,
  updateHrResource,
} from '../store/hrSlice';
import { buildStaffPayload, createInitialStaffForm, normalizeStaffForForm, StaffForm } from '../components/StaffForm';

export function StaffDirectoryPage() {
  const dispatch = useAppDispatch();
  const staffState = useAppSelector((state) => state.hr.resources.staff);
  const options = useAppSelector((state) => state.hr.options);
  const [formValues, setFormValues] = useState(createInitialStaffForm());

  useEffect(() => {
    dispatch(fetchHrOptions());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchHrResource({
      resource: 'staff',
      params: {
        per_page: 10,
        page: staffState.pagination.page,
        ...Object.fromEntries(Object.entries(staffState.filters || {}).filter(([, value]) => value !== '' && value !== undefined)),
      },
    }));
  }, [dispatch, staffState.filters, staffState.pagination.page]);

  async function handleSubmit(event, validationErrors) {
    event.preventDefault();
    if (Object.keys(validationErrors).length > 0) {
      return;
    }

    const payload = buildStaffPayload(formValues);
    const action = formValues.id
      ? updateHrResource({ resource: 'staff', id: formValues.id, payload })
      : createHrResource({ resource: 'staff', payload });

    const result = await dispatch(action);
    if (!result.error) {
      setFormValues(createInitialStaffForm());
      dispatch(fetchHrOptions());
    }
  }

  return (
    <Stack spacing={3}>
      <PermissionGate permission="hr.manage">
        <StaffForm
          values={formValues}
          onChange={(key, value) => setFormValues((current) => ({ ...current, [key]: value }))}
          onSubmit={handleSubmit}
          options={options}
          saving={staffState.saving}
          error={staffState.error}
          submitLabel={formValues.id ? 'Update Staff Member' : 'Create Staff Member'}
        />
      </PermissionGate>

      <Alert severity="info">
        This directory doubles as the teacher pool for Academic Management, so teaching staff records should stay complete here.
      </Alert>

      <AppDataTable
        title="Staff Directory"
        columns={[
          { key: 'employee_code', header: 'Employee Code' },
          { key: 'full_name', header: 'Name' },
          { key: 'department', header: 'Department', render: (row) => row.department?.name || 'Unassigned' },
          { key: 'designation', header: 'Designation', render: (row) => row.designation?.name || 'Unassigned' },
          { key: 'staff_type', header: 'Type' },
          { key: 'employment_type', header: 'Employment' },
          { key: 'current_status', header: 'Status', render: (row) => <Chip label={row.current_status} size="small" color={row.current_status === 'active' ? 'success' : 'default'} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <IconButton component={Link} to={`/hr/staff/${row.id}`} color="primary">
                  <OpenInNewOutlinedIcon />
                </IconButton>
                <PermissionGate permission="hr.manage" fallback={null}>
                  <>
                    <Button size="small" onClick={() => setFormValues(normalizeStaffForForm(row))}>Edit</Button>
                    <IconButton color="error" onClick={() => dispatch(deleteHrResource({ resource: 'staff', id: row.id }))}>
                      <DeleteOutlineOutlinedIcon />
                    </IconButton>
                  </>
                </PermissionGate>
              </Stack>
            ),
          },
        ]}
        rows={staffState.items}
        loading={staffState.loading}
        searchValue={staffState.filters.search || ''}
        onSearchChange={(value) => {
          dispatch(setHrResourceFilters({ resource: 'staff', filters: { search: value } }));
          dispatch(setHrResourcePage({ resource: 'staff', page: 1 }));
        }}
        filters={[
          {
            key: 'department_id',
            label: 'Department',
            value: staffState.filters.department_id || '',
            onChange: () => {},
            options: [{ value: '', label: 'All' }, ...(options.departments || []).map((item) => ({ value: item.id, label: item.name }))],
          },
          {
            key: 'designation_id',
            label: 'Designation',
            value: staffState.filters.designation_id || '',
            onChange: () => {},
            options: [{ value: '', label: 'All' }, ...(options.designations || []).map((item) => ({ value: item.id, label: item.name }))],
          },
          {
            key: 'staff_type',
            label: 'Staff Type',
            value: staffState.filters.staff_type || '',
            onChange: () => {},
            options: [{ value: '', label: 'All' }, { value: 'teaching', label: 'Teaching' }, { value: 'non_teaching', label: 'Non Teaching' }, { value: 'admin', label: 'Admin' }, { value: 'support', label: 'Support' }, { value: 'driver', label: 'Driver' }, { value: 'other', label: 'Other' }],
          },
          {
            key: 'current_status',
            label: 'Status',
            value: staffState.filters.current_status || '',
            onChange: () => {},
            options: [{ value: '', label: 'All' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }, { value: 'resigned', label: 'Resigned' }, { value: 'terminated', label: 'Terminated' }, { value: 'retired', label: 'Retired' }, { value: 'suspended', label: 'Suspended' }],
          },
        ].map((filter) => ({
          ...filter,
          onChange: (value) => {
            dispatch(setHrResourceFilters({ resource: 'staff', filters: { [filter.key]: value } }));
            dispatch(setHrResourcePage({ resource: 'staff', page: 1 }));
          },
        }))}
        pagination={{
          page: staffState.pagination.page,
          totalPages: staffState.pagination.totalPages,
          onPageChange: (page) => dispatch(setHrResourcePage({ resource: 'staff', page })),
        }}
        emptyState="No staff records found."
      />
    </Stack>
  );
}
