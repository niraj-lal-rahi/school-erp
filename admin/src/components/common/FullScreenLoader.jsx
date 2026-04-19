import { CircularProgress, Stack } from '@mui/material';

export function FullScreenLoader() {
  return (
    <Stack minHeight="100vh" alignItems="center" justifyContent="center">
      <CircularProgress />
    </Stack>
  );
}
