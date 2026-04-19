import { Stack } from '@mui/material';

export function StudentFilters({ children }) {
  return (
    <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
      {children}
    </Stack>
  );
}
