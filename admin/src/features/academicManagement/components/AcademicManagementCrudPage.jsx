import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import ToggleOnOutlinedIcon from '@mui/icons-material/ToggleOnOutlined';
import { IconButton, Stack } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createAcademicResource,
  deleteAcademicResource,
  fetchAcademicOptions,
  fetchAcademicResource,
  resetAcademicCurrent,
  updateAcademicResource,
  updateAcademicYearStatus,
} from '../store/academicManagementSlice';
import { AcademicManagementForm } from './AcademicManagementForm';

export function AcademicManagementCrudPage({
  resource,
  title,
  description,
  fields,
  columns,
  filters = [],
  transformBeforeSubmit = (values) => values,
  normalizeRecord = (record) => record,
  permission = 'academic-management.manage',
  allowAcademicYearActivation = false,
}) {
  const dispatch = useAppDispatch();
  const [formValues, setFormValues] = useState({});
  const [search, setSearch] = useState('');
  const [activeFilters, setActiveFilters] = useState({});

  const resourceState = useAppSelector((state) => state.academicManagement.resources[resource]);
  const options = useAppSelector((state) => state.academicManagement.options);

  useEffect(() => {
    dispatch(fetchAcademicOptions());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchAcademicResource({
      resource,
      params: {
        search: search || undefined,
        ...Object.fromEntries(Object.entries(activeFilters).filter(([, value]) => value !== '' && value !== undefined)),
      },
    }));
  }, [dispatch, resource, search, activeFilters]);

  useEffect(() => {
    setFormValues({});
    dispatch(resetAcademicCurrent(resource));
  }, [dispatch, resource]);

  const renderedColumns = useMemo(() => [
    ...columns,
    {
      key: 'actions',
      header: 'Actions',
      render: (row) => (
        <PermissionGate permission={permission} fallback={null}>
          <Stack direction="row" spacing={1}>
            <IconButton color="primary" onClick={() => setFormValues(normalizeRecord(row))}>
              <EditOutlinedIcon />
            </IconButton>
            {allowAcademicYearActivation ? (
              <IconButton
                color="secondary"
                onClick={() => dispatch(updateAcademicYearStatus({
                  id: row.id,
                  payload: {
                    is_active: !row.is_active,
                    status: !row.is_active ? 'active' : 'inactive',
                  },
                }))}
              >
                <ToggleOnOutlinedIcon />
              </IconButton>
            ) : null}
            <IconButton color="error" onClick={() => dispatch(deleteAcademicResource({ resource, id: row.id }))}>
              <DeleteOutlineOutlinedIcon />
            </IconButton>
          </Stack>
        </PermissionGate>
      ),
    },
  ], [allowAcademicYearActivation, columns, dispatch, normalizeRecord, permission, resource]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = transformBeforeSubmit(formValues, options);
    const action = formValues.id
      ? updateAcademicResource({ resource, id: formValues.id, payload })
      : createAcademicResource({ resource, payload });

    const result = await dispatch(action);
    if (!result.error) {
      setFormValues({});
      dispatch(fetchAcademicResource({ resource, params: { search: search || undefined, ...activeFilters } }));
    }
  }

  return (
    <Stack spacing={3}>
      <PermissionGate permission={permission}>
        <AcademicManagementForm
          title={title}
          description={description}
          fields={fields}
          values={formValues}
          onChange={(key, value) => setFormValues((current) => ({ ...current, [key]: value }))}
          onSubmit={handleSubmit}
          saving={resourceState.saving}
          error={resourceState.error}
          options={options}
        />
      </PermissionGate>

      <AppDataTable
        title={`${title} List`}
        columns={renderedColumns}
        rows={resourceState.items}
        loading={resourceState.loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={filters.map((filter) => ({
          key: filter.key,
          label: filter.label,
          value: activeFilters[filter.key] ?? '',
          onChange: (value) => setActiveFilters((current) => ({ ...current, [filter.key]: value })),
          options: filter.options ?? (options[filter.optionsKey] || []).map((item) => filter.mapOption ? filter.mapOption(item) : ({ value: item.id, label: item.name || item.title || item.code })),
        }))}
        pagination={{
          page: resourceState.pagination.page,
          totalPages: resourceState.pagination.totalPages,
          onPageChange: () => {},
        }}
      />
    </Stack>
  );
}
