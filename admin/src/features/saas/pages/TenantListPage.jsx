import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import { Alert, Button, Chip, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { Link as RouterLink } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SaasPageShell } from '../components/SaasPageShell';
import { fetchTenants } from '../store/saasSlice';
import { tenantStatusOptions } from '../types/options';

export function TenantListPage() {
  const dispatch = useAppDispatch();
  const { tenants, tenantsPagination, loading, error } = useAppSelector((state) => state.saas);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  useEffect(() => {
    dispatch(fetchTenants({ search, status, page: tenantsPagination.page }));
  }, [dispatch, search, status, tenantsPagination.page]);

  return (
    <SaasPageShell
      title="Tenant List"
      description="Browse every onboarded school, check subscription posture, and jump into the tenant record you need to manage."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Schools"
        columns={[
          { key: 'name', header: 'School' },
          { key: 'code', header: 'Code' },
          { key: 'status', header: 'Status', render: (row) => <Chip size="small" label={row.status} color={row.status === 'active' ? 'success' : row.status === 'trial' ? 'warning' : 'default'} /> },
          { key: 'email', header: 'Email', render: (row) => row.email || 'N/A' },
          { key: 'subscription', header: 'Plan', render: (row) => row.active_subscription?.subscription_plan?.name || 'Trial / None' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button
                  size="small"
                  variant="outlined"
                  component={RouterLink}
                  to={`/saas/tenants/${row.id}`}
                  startIcon={<VisibilityOutlinedIcon />}
                >
                  Open
                </Button>
              </Stack>
            ),
          },
        ]}
        rows={tenants}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: status,
            onChange: setStatus,
            options: tenantStatusOptions,
          },
        ]}
        pagination={{
          ...tenantsPagination,
          onPageChange: (page) => dispatch(fetchTenants({ search, status, page })),
        }}
        emptyState="No tenants match the current search."
      />
    </SaasPageShell>
  );
}
