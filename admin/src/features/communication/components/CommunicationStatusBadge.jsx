import { Chip } from '@mui/material';

const colorMap = {
  draft: 'default',
  active: 'success',
  inactive: 'default',
  scheduled: 'warning',
  processing: 'warning',
  published: 'success',
  sent: 'success',
  delivered: 'success',
  read: 'info',
  pending: 'warning',
  failed: 'error',
  cancelled: 'default',
  archived: 'default',
  urgent: 'error',
};

function toLabel(value) {
  return String(value || '')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

export function CommunicationStatusBadge({ value }) {
  return (
    <Chip
      size="small"
      label={toLabel(value || 'unknown')}
      color={colorMap[value] || 'default'}
      variant={colorMap[value] ? 'filled' : 'outlined'}
    />
  );
}
