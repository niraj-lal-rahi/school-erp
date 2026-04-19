import { Box, Container, Grid } from '@mui/material';
import { Outlet } from 'react-router-dom';
import { SidebarNav } from '../components/layout/SidebarNav';
import { TopBar } from '../components/layout/TopBar';

export function AdminLayout() {
  return (
    <Container maxWidth="xl" sx={{ py: 3 }}>
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 3, lg: 2.5 }}>
          <Box sx={{ position: { md: 'sticky' }, top: 24, height: '100%' }}>
            <SidebarNav />
          </Box>
        </Grid>
        <Grid size={{ xs: 12, md: 9, lg: 9.5 }}>
          <Box display="grid" gap={3}>
            <TopBar />
            <Outlet />
          </Box>
        </Grid>
      </Grid>
    </Container>
  );
}
