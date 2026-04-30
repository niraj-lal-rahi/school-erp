import { Chip } from '@mui/material';

const colorByType = {
  attendance: 'success',
  fee: 'warning',
  result: 'primary',
  announcement: 'secondary',
  transport: 'info',
  message: 'default',
  general: 'default',
};

export function PortalNotificationBadge({ value }) {
  return (
    <Chip
      size="small"
      label={value || 'general'}
      color={colorByType[value] || 'default'}
      variant="outlined"
      sx={{ textTransform: 'capitalize' }}
    />
  );
}
