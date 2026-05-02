import { Alert, Paper, Stack, Typography } from '@mui/material';
import { RbacPageShell } from '../components/RbacPageShell';

export function AuditLogsPage() {
  return (
    <RbacPageShell
      title="Audit Logs"
      description="Track role, permission, and user-role changes from one place so access drift is easier to explain during support and compliance reviews."
    >
      <Alert severity="info">
        The current backend RBAC API does not expose a dedicated audit-log endpoint yet. This screen is wired into the module now so we can plug that feed in cleanly as soon as the endpoint is added.
      </Alert>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={1}>
          <Typography variant="h6">Planned Feed</Typography>
          <Typography variant="body2" color="text.secondary">
            When the API is available, this page will list role creations, permission syncs, user-role assignments, clones, and deletes with actor, tenant, IP, and before/after payload snapshots.
          </Typography>
        </Stack>
      </Paper>
    </RbacPageShell>
  );
}
