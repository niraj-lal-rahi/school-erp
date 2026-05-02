import AutorenewOutlinedIcon from '@mui/icons-material/AutorenewOutlined';
import { Alert, Button, Chip } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { RbacPageShell } from '../components/RbacPageShell';
import { useRbacAccess } from '../hooks/useRbacAccess';
import { fetchPermissions, syncPermissionsCatalog } from '../store/rbacSlice';

const moduleOptions = [
  { label: 'All', value: '' },
  { label: 'Academic', value: 'academic' },
  { label: 'Student', value: 'student' },
  { label: 'HR', value: 'hr' },
  { label: 'Finance', value: 'finance' },
  { label: 'Attendance', value: 'attendance' },
  { label: 'Timetable', value: 'timetable' },
  { label: 'Transport', value: 'transport' },
  { label: 'Communication', value: 'communication' },
  { label: 'Exams', value: 'exams' },
  { label: 'Reports', value: 'reports' },
  { label: 'Portal', value: 'portal' },
  { label: 'RBAC', value: 'rbac' },
];

const statusOptions = [
  { label: 'All', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
];

export function PermissionsListPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useRbacAccess();
  const { permissions, permissionsPagination, loading, saving, error } = useAppSelector((state) => state.rbac);
  const [search, setSearch] = useState('');
  const [filters, setFilters] = useState({ module: '', status: '' });

  useEffect(() => {
    dispatch(fetchPermissions({
      search: search || undefined,
      module: filters.module || undefined,
      status: filters.status || undefined,
      per_page: 25,
      page: permissionsPagination.page,
    }));
  }, [dispatch, filters, permissionsPagination.page, search]);

  return (
    <RbacPageShell
      title="Permissions List"
      description="Audit the canonical permission catalog, watch the module and action patterns, and resync the default matrix when new modules land."
      actions={canManage ? (
        <Button variant="contained" startIcon={<AutorenewOutlinedIcon />} onClick={() => dispatch(syncPermissionsCatalog())} disabled={saving}>
          {saving ? 'Syncing...' : 'Sync Permissions'}
        </Button>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="System Permissions"
        columns={[
          { key: 'name', header: 'Name' },
          { key: 'code', header: 'Code' },
          { key: 'module', header: 'Module' },
          { key: 'action', header: 'Action' },
          { key: 'status', header: 'Status', render: (row) => <Chip size="small" label={row.status} color={row.status === 'active' ? 'success' : 'default'} /> },
          { key: 'is_system', header: 'Scope', render: (row) => <Chip size="small" label={row.is_system ? 'System' : 'Custom'} color={row.is_system ? 'secondary' : 'default'} /> },
        ]}
        rows={permissions}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={[
          {
            key: 'module',
            label: 'Module',
            value: filters.module,
            onChange: (value) => setFilters((current) => ({ ...current, module: value })),
            options: moduleOptions,
          },
          {
            key: 'status',
            label: 'Status',
            value: filters.status,
            onChange: (value) => setFilters((current) => ({ ...current, status: value })),
            options: statusOptions,
          },
        ]}
        pagination={{
          ...permissionsPagination,
          onPageChange: (page) => dispatch(fetchPermissions({
            search: search || undefined,
            module: filters.module || undefined,
            status: filters.status || undefined,
            per_page: 25,
            page,
          })),
        }}
        emptyState="No permissions matched the current filters."
      />
    </RbacPageShell>
  );
}
