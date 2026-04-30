import { Paper, Stack, Typography } from '@mui/material';

export function PortalSectionCard({ title, subtitle, children }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2}>
        <Stack spacing={0.5}>
          <Typography variant="h6">{title}</Typography>
          {subtitle ? (
            <Typography variant="body2" color="text.secondary">
              {subtitle}
            </Typography>
          ) : null}
        </Stack>
        {children}
      </Stack>
    </Paper>
  );
}
