import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import ContentCopyOutlinedIcon from '@mui/icons-material/ContentCopyOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Chip, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { RbacPageShell } from '../components/RbacPageShell';
import { useRbacAccess } from '../hooks/useRbacAccess';
import { cloneRole, deleteRole, fetchRoles } from '../store/rbacSlice';

const roleTypeOptions = [
  { label: 'All', value: '' },
  { label: 'System', value: 'system' },
  { label: 'Tenant', value: 'tenant' },
];

const statusOptions = [
  { label: 'All', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
];

export function RolesListPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { canManage } = useRbacAccess();
  const { roles, rolesPagination, loading, saving, error } = useAppSelector((state) => state.rbac);
  const [search, setSearch] = useState('');
  const [filters, setFilters] = useState({ role_type: '', status: '' });

  useEffect(() => {
    dispatch(fetchRoles({
      search: search || undefined,
      role_type: filters.role_type || undefined,
      status: filters.status || undefined,
      per_page: 20,
      page: rolesPagination.page,
    }));
  }, [dispatch, filters, rolesPagination.page, search]);

  return (
    <RbacPageShell
      title="Roles List"
      description="Review system and tenant roles, compare their reach, and jump into editing or cloning without leaving the RBAC workspace."
      actions={canManage ? (
        <Button variant="contained" startIcon={<AddOutlinedIcon />} onClick={() => navigate('/rbac/roles/new')}>
          Create Role
        </Button>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="RBAC Roles"
        columns={[
          { key: 'name', header: 'Role' },
          { key: 'code', header: 'Code' },
          { key: 'role_type', header: 'Type', render: (row) => <Chip size="small" label={row.role_type || 'tenant'} color={row.role_type === 'system' ? 'secondary' : 'primary'} /> },
          { key: 'status', header: 'Status', render: (row) => <Chip size="small" label={row.status} color={row.status === 'active' ? 'success' : 'default'} /> },
          { key: 'permissions_count', header: 'Permissions' },
          { key: 'users_count', header: 'Users' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => canManage ? (
              <Stack direction="row" spacing={1}>
                <Button size="small" startIcon={<EditOutlinedIcon />} onClick={() => navigate(`/rbac/roles/${row.id}/edit`)}>
                  Edit
                </Button>
                <Button
                  size="small"
                  startIcon={<ContentCopyOutlinedIcon />}
                  onClick={() => dispatch(cloneRole({
                    id: row.id,
                    payload: {
                      name: `${row.name} Copy`,
                      code: `${row.code}_copy`,
                      description: row.description || `Clone of ${row.name}`,
                    },
                  }))}
                  disabled={saving}
                >
                  Clone
                </Button>
                <Button size="small" color="error" startIcon={<DeleteOutlineOutlinedIcon />} onClick={() => dispatch(deleteRole({ id: row.id }))}>
                  Delete
                </Button>
              </Stack>
            ) : 'View only',
          },
        ]}
        rows={roles}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={[
          {
            key: 'role_type',
            label: 'Role Type',
            value: filters.role_type,
            onChange: (value) => setFilters((current) => ({ ...current, role_type: value })),
            options: roleTypeOptions,
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
          ...rolesPagination,
          onPageChange: (page) => dispatch(fetchRoles({
            search: search || undefined,
            role_type: filters.role_type || undefined,
            status: filters.status || undefined,
            per_page: 20,
            page,
          })),
        }}
        emptyState="No roles found for the current filter set."
      />
    </RbacPageShell>
  );
}
