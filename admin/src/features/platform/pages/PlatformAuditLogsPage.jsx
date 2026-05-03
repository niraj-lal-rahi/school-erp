import HistoryOutlinedIcon from '@mui/icons-material/HistoryOutlined';
import { Alert, Paper, Stack, Typography } from '@mui/material';
import { PlatformPageShell } from '../components/PlatformPageShell';

export function PlatformAuditLogsPage() {
  return (
    <PlatformPageShell
      title="Platform Audit Logs"
      description="Track super-admin activity, tenant lifecycle changes, provisioning events, and database connection failures from the central control plane."
    >
      <Alert severity="warning">
        The backend platform audit service is active, but a dedicated `/api/v1/platform/audit-logs` endpoint has not been exposed yet. This screen is intentionally a readiness view so we don’t fake a working audit table before the API exists.
      </Alert>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Stack direction="row" spacing={1.5} alignItems="center">
            <HistoryOutlinedIcon color="primary" />
            <Typography variant="h6">Central Audit Coverage</Typography>
          </Stack>
          <Typography variant="body2" color="text.secondary">
            The platform audit layer now records tenant creation, lifecycle changes, provisioning success and rollback, tenant database connection tests, super-admin login activity, and tenant DB connection failures. Once the listing endpoint is exposed, this page can switch from readiness mode to a searchable timeline immediately.
          </Typography>
        </Stack>
      </Paper>
    </PlatformPageShell>
  );
}
