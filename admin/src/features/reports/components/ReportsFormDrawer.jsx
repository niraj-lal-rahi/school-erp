import CloseOutlinedIcon from '@mui/icons-material/CloseOutlined';
import { Box, Drawer, IconButton, Stack, Typography } from '@mui/material';

export function ReportsFormDrawer({ open, title, subtitle, onClose, children }) {
  return (
    <Drawer anchor="right" open={open} onClose={onClose}>
      <Box sx={{ width: { xs: '100vw', sm: 520 }, p: 3 }}>
        <Stack direction="row" justifyContent="space-between" alignItems="flex-start" spacing={2} mb={3}>
          <Stack spacing={0.5}>
            <Typography variant="h6">{title}</Typography>
            {subtitle ? <Typography variant="body2" color="text.secondary">{subtitle}</Typography> : null}
          </Stack>
          <IconButton onClick={onClose}>
            <CloseOutlinedIcon />
          </IconButton>
        </Stack>
        {children}
      </Box>
    </Drawer>
  );
}
