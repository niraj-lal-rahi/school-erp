import { Box, Paper, Stack, Typography } from '@mui/material';

export function ReportsMiniBarChart({ title, subtitle, items = [], valueFormatter = (value) => value }) {
  const max = Math.max(...items.map((item) => Number(item.value || 0)), 0);

  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={0.5} mb={2}>
        <Typography variant="h6">{title}</Typography>
        {subtitle ? <Typography variant="body2" color="text.secondary">{subtitle}</Typography> : null}
      </Stack>

      <Stack spacing={1.5}>
        {items.length ? items.map((item) => {
          const width = max > 0 ? (Number(item.value || 0) / max) * 100 : 0;
          return (
            <Stack key={item.label} spacing={0.5}>
              <Stack direction="row" justifyContent="space-between" spacing={2}>
                <Typography variant="body2">{item.label}</Typography>
                <Typography variant="body2" color="text.secondary">{valueFormatter(item.value)}</Typography>
              </Stack>
              <Box sx={{ height: 10, borderRadius: 999, bgcolor: 'rgba(20,33,61,0.08)', overflow: 'hidden' }}>
                <Box
                  sx={{
                    width: `${width}%`,
                    height: '100%',
                    borderRadius: 999,
                    background: 'linear-gradient(90deg, #0b6e4f 0%, #2ca58d 100%)',
                  }}
                />
              </Box>
            </Stack>
          );
        }) : (
          <Typography variant="body2" color="text.secondary">No chart data available.</Typography>
        )}
      </Stack>
    </Paper>
  );
}
