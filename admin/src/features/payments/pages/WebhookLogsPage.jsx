import { Alert, Paper, Stack, Typography } from '@mui/material';
import { PaymentsPageShell } from '../components/PaymentsPageShell';

export function WebhookLogsPage() {
  return (
    <PaymentsPageShell
      title="Webhook Logs"
      description="Keep webhook operations visible while the dedicated event-log endpoint catches up with the payment processing layer."
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Alert severity="info">
            The backend currently accepts and processes Razorpay and Stripe webhooks, but it does not yet expose a dedicated
            `/api/v1/payments/webhook-events` read endpoint for this screen.
          </Alert>
          <Typography variant="body2" color="text.secondary">
            This page is intentionally wired as a readiness surface instead of pretending data is available. Once the API adds a webhook-events listing route,
            we can connect this screen directly without reworking the rest of the payments module.
          </Typography>
        </Stack>
      </Paper>
    </PaymentsPageShell>
  );
}
