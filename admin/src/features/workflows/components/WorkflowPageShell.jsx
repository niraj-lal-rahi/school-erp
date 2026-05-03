import { Paper, Stack, Typography } from '@mui/material';

export function WorkflowPageShell({ title, description, actions, children }) {
  return (
    <Stack spacing={3}>
      <Paper
        elevation={0}
        sx={{
          p: 3,
          border: '1px solid rgba(20,33,61,0.08)',
          background: 'linear-gradient(135deg, #ffffff 0%, #eef7f2 100%)',
        }}
      >
        <Stack
          direction={{ xs: 'column', lg: 'row' }}
          justifyContent="space-between"
          alignItems={{ xs: 'flex-start', lg: 'center' }}
          spacing={2}
        >
          <Stack spacing={1}>
            <Typography variant="h5">{title}</Typography>
            <Typography variant="body2" color="text.secondary">
              {description}
            </Typography>
          </Stack>
          {actions}
        </Stack>
      </Paper>
      {children}
    </Stack>
  );
}
