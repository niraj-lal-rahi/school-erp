import { LinearProgress, Paper, Stack, Typography } from '@mui/material';

export function PerformanceMetricCard({ label, value, helper, progress = 0, color = 'primary' }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={1.5}>
        <Typography variant="overline" color="text.secondary">
          {label}
        </Typography>
        <Typography variant="h4">{value}</Typography>
        <LinearProgress variant="determinate" value={Math.max(0, Math.min(100, Number(progress) || 0))} color={color} sx={{ height: 8, borderRadius: 999 }} />
        {helper ? (
          <Typography variant="body2" color="text.secondary">
            {helper}
          </Typography>
        ) : null}
      </Stack>
    </Paper>
  );
}
