import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { IconButton, Stack } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { validateRequiredFields } from '../../../utils/hrValidation';
import {
  createHrResource,
  deleteHrResource,
  fetchHrOptions,
  fetchHrResource,
  setHrResourceFilters,
  setHrResourcePage,
  updateHrResource,
} from '../store/hrSlice';
import { HrCrudForm } from './HrCrudForm';

function buildInitialValues(fields) {
  return fields.reduce((carry, field) => ({
    ...carry,
    [field.key]: field.initialValue ?? '',
  }), { id: null });
}

export function HrCrudPage({
  resource,
  title,
  description,
  fields,
  columns,
  permission = 'hr.manage',
  normalizeRecord = (record) => record,
  transformBeforeSubmit = (values) => values,
  filters = [],
  emptyState,
}) {
  const dispatch = useAppDispatch();
  const resourceState = useAppSelector((state) => state.hr.resources[resource]);
  const options = useAppSelector((state) => state.hr.options);
  const [formValues, setFormValues] = useState(buildInitialValues(fields));
  const [validationErrors, setValidationErrors] = useState({});

  useEffect(() => {
    dispatch(fetchHrOptions());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchHrResource({
      resource,
      params: {
        per_page: 10,
        page: resourceState.pagination.page,
        ...Object.fromEntries(Object.entries(resourceState.filters || {}).filter(([, value]) => value !== '' && value !== undefined)),
      },
    }));
  }, [dispatch, resource, resourceState.filters, resourceState.pagination.page]);

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
            <IconButton color="error" onClick={() => dispatch(deleteHrResource({ resource, id: row.id }))}>
              <DeleteOutlineOutlinedIcon />
            </IconButton>
          </Stack>
        </PermissionGate>
      ),
    },
  ], [columns, dispatch, normalizeRecord, permission, resource]);

  async function handleSubmit(event) {
    event.preventDefault();
    const errors = validateRequiredFields(formValues, fields);
    setValidationErrors(errors);

    if (Object.keys(errors).length > 0) {
      return;
    }

    const payload = transformBeforeSubmit(formValues);
    const action = formValues.id
      ? updateHrResource({ resource, id: formValues.id, payload })
      : createHrResource({ resource, payload });

    const result = await dispatch(action);
    if (!result.error) {
      setFormValues(buildInitialValues(fields));
      setValidationErrors({});
      dispatch(fetchHrOptions());
      dispatch(fetchHrResource({
        resource,
        params: {
          per_page: 10,
          page: resourceState.pagination.page,
          ...Object.fromEntries(Object.entries(resourceState.filters || {}).filter(([, value]) => value !== '' && value !== undefined)),
        },
      }));
    }
  }

  return (
    <Stack spacing={3}>
      <PermissionGate permission={permission}>
        <HrCrudForm
          title={title}
          description={description}
          fields={fields.map((field) => ({
            ...field,
            options: typeof field.options === 'function' ? field.options(options) : field.options,
          }))}
          values={formValues}
          onChange={(key, value) => setFormValues((current) => ({ ...current, [key]: value }))}
          onSubmit={handleSubmit}
          submitLabel={formValues.id ? `Update ${title}` : `Create ${title}`}
          saving={resourceState.saving}
          error={resourceState.error}
          validationErrors={validationErrors}
        />
      </PermissionGate>

      <AppDataTable
        title={`${title} Directory`}
        columns={renderedColumns}
        rows={resourceState.items}
        loading={resourceState.loading}
        searchValue={resourceState.filters.search || ''}
        onSearchChange={(value) => {
          dispatch(setHrResourceFilters({ resource, filters: { search: value } }));
          dispatch(setHrResourcePage({ resource, page: 1 }));
        }}
        filters={filters.map((filter) => ({
          key: filter.key,
          label: filter.label,
          value: resourceState.filters[filter.key] ?? '',
          onChange: (value) => {
            dispatch(setHrResourceFilters({ resource, filters: { [filter.key]: value } }));
            dispatch(setHrResourcePage({ resource, page: 1 }));
          },
          options: typeof filter.options === 'function' ? filter.options(options) : filter.options,
        }))}
        pagination={{
          page: resourceState.pagination.page,
          totalPages: resourceState.pagination.totalPages,
          onPageChange: (page) => dispatch(setHrResourcePage({ resource, page })),
        }}
        emptyState={emptyState}
      />
    </Stack>
  );
}
