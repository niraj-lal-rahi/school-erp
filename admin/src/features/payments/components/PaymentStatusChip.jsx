import { Chip } from '@mui/material';

const STATUS_COLOR_MAP = {
  pending: 'warning',
  initiated: 'info',
  successful: 'success',
  failed: 'error',
  cancelled: 'default',
  refunded: 'secondary',
  manually_verified: 'success',
  verified: 'success',
  manual_review: 'warning',
  processing: 'info',
  requested: 'warning',
  paid: 'success',
  expired: 'default',
};

export function PaymentStatusChip({ value }) {
  return (
    <Chip
      size="small"
      label={String(value || 'unknown').replaceAll('_', ' ')}
      color={STATUS_COLOR_MAP[value] || 'default'}
      sx={{ textTransform: 'capitalize' }}
    />
  );
}
