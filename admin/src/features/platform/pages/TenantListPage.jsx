import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import { Alert, Button, Chip, Stack } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { Link as RouterLink } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PlatformPageShell } from '../components/PlatformPageShell';
import { platformApi } from '../services/platformApi';

const statusOptions = [
  { value: '', label: 'All statuses' },
  { value: 'trial', label: 'Trial' },
  { value: 'active', label: 'Active' },
  { value: 'suspended', label: 'Suspended' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'expired', label: 'Expired' },
];

export function TenantListPage() {
  const [tenants, setTenants] = useState([]);
  const [pagination, setPagination] = useState({ page: 1, totalPages: 1 });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  useEffect(() => {
    let active = true;

    async function load() {
      setLoading(true);
      setError('');

      try {
        const response = await platformApi.getTenants({
          search: search || undefined,
          status: status || undefined,
          page: pagination.page,
        });
        const payload = response.data?.data || response.data || {};
        const rows = Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : [];
        const meta = payload?.meta || response.data?.meta || {};

        if (!active) {
          return;
        }

        setTenants(rows);
        setPagination((current) => ({
          ...current,
          page: meta.current_page || current.page,
          totalPages: meta.last_page || 1,
        }));
      } catch (requestError) {
        if (!active) {
          return;
        }

        setError(requestError?.response?.data?.message || 'Unable to load platform tenants right now.');
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    }

    load();

    return () => {
      active = false;
    };
  }, [pagination.page, search, status]);

  const rows = useMemo(() => tenants, [tenants]);

  return (
    <PlatformPageShell
      title="Tenant List"
      description="Review every school in the platform control plane, monitor lifecycle state, and jump into the isolated database posture for a specific tenant."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Platform Tenants"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={(value) => {
          setPagination((current) => ({ ...current, page: 1 }));
          setSearch(value);
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: status,
            onChange: (value) => {
              setPagination((current) => ({ ...current, page: 1 }));
              setStatus(value);
            },
            options: statusOptions,
          },
        ]}
        pagination={{
          ...pagination,
          onPageChange: (page) => setPagination((current) => ({ ...current, page })),
        }}
        emptyState="No platform tenants match the current search."
        columns={[
          { key: 'name', header: 'School' },
          { key: 'code', header: 'Code' },
          { key: 'slug', header: 'Slug' },
          {
            key: 'status',
            header: 'Status',
            render: (row) => (
              <Chip
                size="small"
                label={row.status}
                color={row.status === 'active' ? 'success' : row.status === 'trial' ? 'warning' : row.status === 'suspended' ? 'error' : 'default'}
              />
            ),
          },
          { key: 'email', header: 'Email', render: (row) => row.email || 'N/A' },
          { key: 'trial_ends_at', header: 'Trial Ends', render: (row) => row.trial_ends_at || 'N/A' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button
                  size="small"
                  variant="outlined"
                  component={RouterLink}
                  to={`/platform/tenants/${row.id}`}
                  startIcon={<VisibilityOutlinedIcon />}
                >
                  Open
                </Button>
              </Stack>
            ),
          },
        ]}
      />
    </PlatformPageShell>
  );
}
