import { LinearProgress, Paper, Stack, Typography } from '@mui/material';

export function ReportsMetricCard({ label, value, helper, progress = null, color = 'primary' }) {
  return (
    <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(20,33,61,0.08)', height: '100%' }}>
      <Stack spacing={1}>
        <Typography variant="body2" color="text.secondary">{label}</Typography>
        <Typography variant="h5">{value}</Typography>
        {helper ? <Typography variant="caption" color="text.secondary">{helper}</Typography> : null}
        {progress !== null ? <LinearProgress variant="determinate" value={Math.max(0, Math.min(100, Number(progress) || 0))} color={color} /> : null}
      </Stack>
    </Paper>
  );
}
