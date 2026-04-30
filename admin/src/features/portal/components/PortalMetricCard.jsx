import { Paper, Stack, Typography } from '@mui/material';

export function PortalMetricCard({ label, value, helper }) {
  return (
    <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={1}>
        <Typography variant="body2" color="text.secondary">
          {label}
        </Typography>
        <Typography variant="h4">{value ?? '-'}</Typography>
        {helper ? (
          <Typography variant="caption" color="text.secondary">
            {helper}
          </Typography>
        ) : null}
      </Stack>
    </Paper>
  );
}
