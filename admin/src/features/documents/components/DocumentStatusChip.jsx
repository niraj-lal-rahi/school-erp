import { Chip } from '@mui/material';

const colors = {
  active: 'success',
  archived: 'default',
  deleted: 'error',
  pending: 'warning',
  verified: 'success',
  rejected: 'error',
  expired: 'default',
  inactive: 'default',
  processing: 'info',
  completed: 'success',
  failed: 'error',
  sent: 'success',
};

export function DocumentStatusChip({ value }) {
  return <Chip size="small" color={colors[value] || 'default'} label={value || 'unknown'} />;
}
