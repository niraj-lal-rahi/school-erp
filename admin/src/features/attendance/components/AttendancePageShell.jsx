import { Paper, Stack, Typography } from '@mui/material';

export function AttendancePageShell({ title, description, children }) {
  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={1}>
          <Typography variant="h5">{title}</Typography>
          <Typography variant="body2" color="text.secondary">
            {description}
          </Typography>
        </Stack>
      </Paper>
      {children}
    </Stack>
  );
}
